
#include <SPI.h>
#include <Ethernet2.h>

/* RS422 card / rotary encoder SSI (25 bits) */
#define AZIMUTH_SSI_CLK_PIN     6     /* clock signal */
#define AZIMUTH_SSI_DATA_PIN    7     /* data line */
#define AZIMUTH_SSI_BITS        24    /* total bits */
#define AZIMUTH_SSI_BITS_SINGLE 10    /* single turn bits */

/* ethernet */
byte mac[] = {
  0x00, 0xAA, 0xBB, 0xCC, 0xCC, 0x01
};
IPAddress ip(192, 168, 0, 31);

/* globals */
int setpoint_pv;

/* pocess value server */
#define SERVER_IP     "192.168.0.4"
#define SERVER_SET_SP PSTR("GET /arduino.php?src=azimuth&sp=%d HTTP/1.1")

void setup() {
  /* serial connection for debugging */
  Serial.begin (19200);

  /* setup the RS422 card */
  pinMode (AZIMUTH_SSI_CLK_PIN, OUTPUT);
  pinMode (AZIMUTH_SSI_DATA_PIN, INPUT_PULLUP);
  digitalWrite (AZIMUTH_SSI_CLK_PIN, HIGH);

  /* wait for ethernet bootup */
  delay (500);

  Ethernet.begin (mac, ip);
  Serial.print ("IP: ");
  Serial.println (Ethernet.localIP ());
}

static int
readEncoder (uint8_t clk_pin, uint8_t data_pin, int *ret_multi)
{
  unsigned long gray = 0;
  unsigned long bin = 0;
  unsigned int  power = 2 << AZIMUTH_SSI_BITS_SINGLE;
  byte          lastbit;

  for (int i = 0; i < AZIMUTH_SSI_BITS; i++)
  {
    /* quickly toggle the clock of the RS422 card */
    bitClear (PORTD, clk_pin); /* write LOW */
    bitSet (PORTD, clk_pin); /* write HIGH */

    /* read the value */
    gray <<= 1;
    gray |= digitalRead (data_pin);
  }

  /* convert gray code to binary */
  lastbit = bitRead (gray, AZIMUTH_SSI_BITS);
  for (unsigned int b = AZIMUTH_SSI_BITS; b > 0; b--)
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
http_send (const char *get)
{
  EthernetClient client;

  if (client.connect (SERVER_IP, 80))
  {
    /* send GET request */
    client.println (get);
    client.println (F("Host: oving.hole"));
    client.println (F("User-Agent: arduino-ethernet"));
    client.println (F("Connection: close"));
    client.println ();
    client.stop ();

    return true;
  }
  
  Serial.println ("connection failed");
  return false;
}

static void
http_send_sp(int pv)
{
  char buf[50];

  sprintf_P (buf, SERVER_SET_SP, pv);

  if (http_send (buf))
  {
    /* update last send pv setpoint */
    setpoint_pv = pv;
  }
}

void loop() {
  int deg;

  /* read the position of the encoder */
  deg = readEncoder(AZIMUTH_SSI_CLK_PIN, AZIMUTH_SSI_DATA_PIN, NULL);

  if (setpoint_pv != deg)
  {
    /* tell the server our new setpoint */
    http_send_sp (deg);
  }

  /* sleep */
  delay(100);
}
