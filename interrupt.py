# call this script without any argument to be able to close it with a keyboard input
import RPi.GPIO as GPIO
from pymodbus.exceptions import ConnectionException
from multiprocessing import Process
import urllib2
import os
import time
import threading
import sys
from pymodbus.client.sync import ModbusTcpClient as ModbusClient
from datetime import datetime

GPIO.setmode(GPIO.BCM)  
  
# GPIO 3 and 15 set up as input. It is pulled up to stop false signals  
GPIO.setup(3 , GPIO.IN, pull_up_down=GPIO.PUD_UP)  
GPIO.setup(15, GPIO.IN, pull_up_down=GPIO.PUD_UP)

# f = open('mislogfile.txt', 'w') #create a file using the given input

def ReadSitePower():
   try:
      response = FroniusWR.read_holding_registers(500,2,unit=1)
      sitePower = response.registers[0] # this only interprets one uint16... (65kW, should be enough)
   except ConnectionException:
      sitePower = 0
   return sitePower

def ReadSitePowerOst():
   try:
      response = FroniusWR.read_holding_registers(40263+41,1,unit=1)
      responseFactor = FroniusWR.read_holding_registers(40263+4,1,unit=1)
      readValueFactor = responseFactor.registers[0]

      if readValueFactor == 0xFFFE:  # 65534
	  sitePower = response.registers[0] / 100 # this only interprets one uint16... (65kW, should be enough)
      elif readValueFactor == 0x8000: # 32768
          sitePower = 0
      else:
          sitePower = response.registers[0] / 10 # this only interprets one uint16... (65kW, should be enough)

   except ConnectionException:
      sitePower = 0
   return sitePower

def ReadSitePowerWest():
   try:
      response = FroniusWR.read_holding_registers(40263+21,1,unit=1)
      responseFactor = FroniusWR.read_holding_registers(40263+4,1,unit=1)
      readValueFactor = responseFactor.registers[0]

      if readValueFactor == 0xFFFE:  # 65534
	  sitePower = response.registers[0] / 100 # this only interprets one uint16... (65kW, should be enough)
      elif readValueFactor == 0x8000: # 32768
          sitePower = 0
      else:
          sitePower = response.registers[0] / 10 # this only interprets one uint16... (65kW, should be enough)

   except ConnectionException:
      sitePower = 0
   return sitePower


def TransmitWorker():
#   print('Prozess ID:', os.getpid())
#   f.write('Transmit worker: Prozess ID:' + str(os.getpid()) + '\n')
#   f.write('Recorded at: %s\n'  %datetime.now())
#   f.flush()
   sitePower = ReadSitePower()
   sitePowerOst = ReadSitePowerOst()
   sitePowerWest = ReadSitePowerWest()

   urlToSet = "http://localhost/pv/web/app.php/insert/meterdata/"
   urlToSet += str(sitePower)
   urlToSet += "/"
   urlToSet += str(sitePowerOst)
   urlToSet += "/"
   urlToSet += str(sitePowerWest)
   urlToSet += "/-10"
   urllib2.urlopen(urlToSet)
   return 1

def ConsumeWorker():
   sitePower = ReadSitePower()
   sitePowerOst = ReadSitePowerOst()
   sitePowerWest = ReadSitePowerWest()
   urlToSet = "http://localhost/pv/web/app.php/insert/meterdata/"
   urlToSet += str(sitePower)
   urlToSet += "/"
   urlToSet += str(sitePowerOst)
   urlToSet += "/"
   urlToSet += str(sitePowerWest)
   urlToSet += "/10"
   urllib2.urlopen(urlToSet)
   return 0

def greenTransmitEnergy(channel):
#   f.write('greenTransmitEnergy: Prozess ID:' + str(os.getpid()) + '\n')
#   f.write('Recorded at: %s\n'  %datetime.now())
   if GPIO.input(3) == GPIO.LOW:
#     f.write('GPIO(3) == GPIO.LOW\n')
     TransmitProcess = Process(target=TransmitWorker)
     TransmitProcess.start()
#   f.flush()

def yellowConsumeEnergy(channel):
   if GPIO.input(15) == GPIO.LOW:
     ConsumeProcess = Process(target=ConsumeWorker)
     ConsumeProcess.start()

if __name__ == '__main__':
    FroniusWR = ModbusClient(host = '192.168.1.38', port=502)
#    f.write('main: Prozess ID:' + str(os.getpid()) + '\n')
#    f.write('Recorded at: %s\n'  %datetime.now())
#    f.flush()

    GPIO.add_event_detect(3 , GPIO.FALLING, callback=greenTransmitEnergy, bouncetime=1000)
    GPIO.add_event_detect(15, GPIO.FALLING, callback=yellowConsumeEnergy, bouncetime=1000)
    # bounce time 50ms ended in several failures...

#initialize session variables:
    urlToSet = "http://localhost/pv/web/app.php/power/init/session"
    urllib2.urlopen(urlToSet)

try:
#   print('Prozess ID:', os.getpid())
   if len(sys.argv) == 1:
       raw_input("Press enter to exit\n")
   else:
       dummy_event = threading.Event()
       dummy_event.wait()
#   raw_input("\nWaiting for falling edge on port 3 and 15\nPress Enter to exit\n")
except KeyboardInterrupt:  
    GPIO.cleanup()       # clean up GPIO on CTRL+C exit  
GPIO.cleanup()
#f.close()
