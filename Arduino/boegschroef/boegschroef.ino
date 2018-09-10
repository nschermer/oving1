
#include <SPI.h>
#include <Ethernet2.h>
#include <limits.h>

/* RS422 card / rotary encoder SSI (25 bits) */
#define SSI_CLK_PIN     6     /* clock signal */
#define SSI_DATA_PIN    7     /* data line */
#define SSI_BITS        24    /* total bits */
#define SSI_BITS_SINGLE 10    /* single turn bits */

/* relais for motor */
#define RELAY_CW_PIN  4
#define RELAY_CCW_PIN 5

/* allowable fault in setpoint (x2) */
#define SETPOINT_FAULT 5

/* enum */
#define STATE_CHECK        0
#define STATE_WAITING      1
#define STATE_ROTATE_CW    2
#define STATE_ROTATE_CCW   3

/* globals */
int state = STATE_CHECK;
int setpoint = INT_MIN;

/* process value updates to server */
int           last_pv = 0;
unsigned long last_pv_millis = 0;
unsigned long last_pv_runout = 0;

/* ethernet */
byte mac[] = {
  0x00, 0xAA, 0xBB, 0xCC, 0xDE, 0x02
};
IPAddress ip(192, 168, 0, 30);

/* pocess value server */
#define SERVER_IP     "192.168.0.4"
#define SERVER_REQ_SP "GET /arduino.php?src=azimuth&pv=null HTTP/1.1"
#define SERVER_SET_PV PSTR("GET /arduino.php?src=azimuth&pv=%d HTTP/1.1")

/* server to receive setpoint */
EthernetServer server(80);

void setup (void)
{
  /* serial connection for debugging */
  Serial.begin (19200);

  /* setup the RS422 card */
  pinMode (SSI_CLK_PIN, OUTPUT);
  pinMode (SSI_DATA_PIN, INPUT_PULLUP);
  digitalWrite (SSI_CLK_PIN, HIGH);

  /* setup relay */
  pinMode (RELAY_CW_PIN, OUTPUT);
  pinMode (RELAY_CCW_PIN, OUTPUT);
  digitalWrite (RELAY_CW_PIN, HIGH);
  digitalWrite (RELAY_CCW_PIN, HIGH);

  /* wait for ethernet bootup */
  delay (500);

  Ethernet.begin (mac, ip);
}

static int
readEncoder (int *ret_multi)
{
  unsigned long gray = 0;
  unsigned long bin = 0;
  unsigned int  power = 2 << SSI_BITS_SINGLE;
  byte          lastbit;

  for (int i = 0; i < SSI_BITS; i++)
  {
    /* quickly toggle the clock of the RS422 card */
    bitClear (PORTD, SSI_CLK_PIN); /* write LOW */
    bitSet (PORTD, SSI_CLK_PIN); /* write HIGH */

    /* read the value */
    gray <<= 1;
    gray |= digitalRead (SSI_DATA_PIN);
  }

  /* convert gray code to binary */
  lastbit = bitRead (gray, SSI_BITS);
  for (unsigned int b = SSI_BITS; b > 0; b--)
  {
    if (lastbit == bitRead (gray, b - 1))
    {
      bitClear (bin, b - 1);
      lastbit = 0;
    }
    else
    {
      bitSet (bin, b - 1);
      lastbit = 1;
    }
  }

  /* return multi turns */
  if (ret_multi != NULL)
    *ret_multi = (bin / power) - (power * 2);

  /* return single turns (in degrees) */
  return (bin % power) * 360UL / power;
}

static boolean
http_send (const char *buf)
{
  EthernetClient client;

  if (client.connect (SERVER_IP, 80))
  {
    /* send GET request */
    client.println (buf);
    client.println (F("Host: oving.hole"));
    client.println (F("User-Agent: arduino-ethernet"));
    client.println (F("Connection: close"));
    client.println ();
    client.stop ();

    return true;
  }

  // Serial.println ("connection failed");

  return false;
}

static void
http_send_pv(int pv)
{
  char buf[50];

  if (pv == INT_MIN)
  {
    http_send (SERVER_REQ_SP);
  }
  else
  {
    sprintf_P (buf, SERVER_SET_PV, pv);
    if (http_send (buf))
    {
      /* update last send pv setpoint */
      last_pv = pv;
    }
  }
}

static boolean
http_receive (void)
{
  String         header;
  String         value;
  EthernetClient client = server.available();
  char           c;
  int            first, last;
  boolean        retval = false;

  if (client)
  {
    header = String(100);

    while (client.connected())
    {
      if (client.available())
      {
        c = client.read();
        if (header.length() < 100)
          header += c;

        if (c == '\n')
        {
          first = header.indexOf(F("sp="));
          if (first > 0)
          {
            last = header.indexOf('&', first);
            if (last == -1)
              last = header.indexOf(' ', first);

            value = header.substring(first + 3, last);

            setpoint = value.toInt();

            retval = true;
          }

          /* reply to client with standard html data to handle request */
          client.println(F("HTTP/1.1 200 OK"));
          client.println(F("Connection: close"));
          client.println();

          break;
        }
      }
    }

    client.stop();
  }

  return retval;
}

void loop(void)
{
  int deg = INT_MIN;
  int new_setpoint = 0, angle;
  int new_state = state;
  boolean force_pv = false;
  unsigned long current_millis = millis();

  if (http_receive())
  {
    /* we received a new setpoint, check encoder position */
    new_state = STATE_CHECK;
  }

  if (setpoint == INT_MIN)
  {
    /* ask server for new setpoint */
    http_send_pv (INT_MIN);
  }
  else if (new_state != STATE_WAITING)
  {
    /* read the position of the encoder */
    deg = readEncoder(NULL);

    angle = setpoint - deg;
    if (angle < -180)
      angle += 360;
    if (angle > 180)
      angle -= 360;

    if (abs (angle) > SETPOINT_FAULT)
    {
      /* determ the (shortest) rotation direction */
      if (angle < 0)
        new_state = STATE_ROTATE_CCW;
      else
        new_state = STATE_ROTATE_CW;
    }
    else
    {
      new_state = STATE_WAITING;
    }

    /* continue updating for atleast 1 second */
    last_pv_runout = current_millis + 1000;
  }
  else if (last_pv_runout > current_millis)
  {
    /* check 1 second after relay stop */
    deg = readEncoder(NULL);
  }
  else if ((current_millis - last_pv_millis) >= 15000)
  {
    /* force server parameter update every 15s */
    deg = readEncoder(NULL);
    force_pv = true;
  }

  if (state != new_state)
  {
    /* switch relays */
    digitalWrite (RELAY_CW_PIN,
                  new_state == STATE_ROTATE_CW ? LOW : HIGH);
    digitalWrite (RELAY_CCW_PIN,
                  new_state == STATE_ROTATE_CCW ? LOW : HIGH);

    state = new_state;
  }

  if (deg != INT_MIN)
  {
    /* forced update or every 250ms if value checked */
    if (force_pv
        || (last_pv != deg
            && (current_millis - last_pv_millis) >= 250))
    {
      http_send_pv (deg);

      last_pv_millis = current_millis;
    }
  }

  /* sleep */
  delay(50);
}
