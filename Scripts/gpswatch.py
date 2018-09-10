#! /usr/bin/python2

from gps import *
import time
import threading
import redis
import json

gpsd = None

class GpsPoller(threading.Thread):
	def __init__(self):
		threading.Thread.__init__(self)
		global gpsd
		gpsd = gps(mode=WATCH_ENABLE)
		self.current_value = None
		self.running = True

	def run(self):
		global gpsd
		while gpsp.running:
			gpsd.next()

if __name__ == '__main__':
	gpsp = GpsPoller()
	
	redis = redis.StrictRedis(host='localhost', port=6379, charset="utf-8", db=1, decode_responses=True)

	try:
		gpsp.start()
		time.sleep(5)
		while True:
			# Dump data in redis
			
			data = {}
			data['lat'] = gpsd.fix.latitude
			data['lon'] = gpsd.fix.longitude
			redis.set("gps/history/%d" % time.time() , json.dumps(data), 300)

			redis.set("gps/lat", gpsd.fix.latitude, 30)
			redis.set("gps/lon", gpsd.fix.longitude, 30)
			redis.set("gps/mode", gpsd.fix.mode, 30)
			redis.set("gps/speed", gpsd.fix.speed, 30)
			redis.set("gps/epx", gpsd.fix.epx, 30)
			redis.set("gps/epy", gpsd.fix.epy, 30)
			redis.set("gps/eps", gpsd.fix.eps, 30)
			redis.set("gps/track", gpsd.fix.track, 30)

			time.sleep(5)
	except (KeyboardInterrupt, SystemExit):
		gpsp.running = False
		gpsp.join()