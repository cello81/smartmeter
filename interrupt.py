import RPi.GPIO as GPIO
from multiprocessing import Process
import urllib2
import os
#import time
import threading
from pymodbus.client.sync import ModbusTcpClient as ModbusClient

GPIO.setmode(GPIO.BCM)  
  
# GPIO 3 and 15 set up as input. It is pulled up to stop false signals  
GPIO.setup(3 , GPIO.IN, pull_up_down=GPIO.PUD_UP)  
GPIO.setup(15, GPIO.IN, pull_up_down=GPIO.PUD_UP)

def ReadSitePower():
   response = FroniusWR.read_input_registers(0,2)
   sitePower = response.registers[0]
   return sitePower


def TransmitWorker():
   print "gruener Taster wurde gedrueckt"
   tariff = 5.9
   sitePower = ReadSitePower()
   print('Prozess ID:' , os.getpid(),'SitePower:', sitePower, 'Tariff', tariff)
   urlToSet = "http://localhost/pv/web/app_dev.php/insert/meterdata/"
   urlToSet += str(sitePower)
   urlToSet += "/-10/"
   urlToSet += str(tariff)
#   print(urlToSet)
   urllib2.urlopen(urlToSet)
   return 1

def ConsumeWorker():
   print "gelber Taster wurde gedrueckt"
   tariff = 21
   sitePower = ReadSitePower()
   print('Prozess ID:' , os.getpid(),'SitePower:', sitePower, 'Tariff', tariff)
   urlToSet = "http://localhost/pv/web/app_dev.php/insert/meterdata/"
   urlToSet += str(sitePower)
   urlToSet += "/10/"
   urlToSet += str(tariff)
   urllib2.urlopen(urlToSet)
   return 0

def greenTransmitEnergy(channel):
   TransmitProcess = Process(target=TransmitWorker)
   TransmitProcess.start()

def yellowConsumeEnergy(channel):
   ConsumeProcess = Process(target=ConsumeWorker)
   ConsumeProcess.start()

if __name__ == '__main__':
    FroniusWR = ModbusClient(host = '192.168.0.101', port=502)
    
    GPIO.add_event_detect(3 , GPIO.FALLING, callback=greenTransmitEnergy, bouncetime=200)
    GPIO.add_event_detect(15, GPIO.FALLING, callback=yellowConsumeEnergy, bouncetime=200)
 
try:
   print('Prozess ID:', os.getpid())
#   while True:
#       time.sleep(5)
   dummy_event = threading.Event()
   dummy_event.wait()
#   raw_input("\nWaiting for falling edge on port 3 and 15\nPress Enter to exit\n")
except KeyboardInterrupt:  
    GPIO.cleanup()       # clean up GPIO on CTRL+C exit  
GPIO.cleanup()           #

