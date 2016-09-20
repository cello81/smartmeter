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

GPIO.setmode(GPIO.BCM)  
  
# GPIO 3 and 15 set up as input. It is pulled up to stop false signals  
GPIO.setup(3 , GPIO.IN, pull_up_down=GPIO.PUD_UP)  
GPIO.setup(15, GPIO.IN, pull_up_down=GPIO.PUD_UP)

def ReadSitePower():
   try:
      response = FroniusWR.read_input_registers(0,2)
      sitePower = response.registers[0]
   except ConnectionException:
      sitePower = -1
   return sitePower

def TransmitWorker():
   sitePower = ReadSitePower()
   urlToSet = "http://localhost/pv/web/app.php/insert/meterdata/"
   urlToSet += str(sitePower)
   urlToSet += "/-10"
   urllib2.urlopen(urlToSet)
   return 1

def ConsumeWorker():
   sitePower = ReadSitePower()
   urlToSet = "http://localhost/pv/web/app.php/insert/meterdata/"
   urlToSet += str(sitePower)
   urlToSet += "/10"
   urllib2.urlopen(urlToSet)
   return 0

def greenTransmitEnergy(channel):
   TransmitProcess = Process(target=TransmitWorker)
   TransmitProcess.start()

def yellowConsumeEnergy(channel):
   ConsumeProcess = Process(target=ConsumeWorker)
   ConsumeProcess.start()

if __name__ == '__main__':
    FroniusWR = ModbusClient(host = '192.168.1.38', port=502)
    
    GPIO.add_event_detect(3 , GPIO.FALLING, callback=greenTransmitEnergy, bouncetime=1000)
    GPIO.add_event_detect(15, GPIO.FALLING, callback=yellowConsumeEnergy, bouncetime=1000)
    # bounce time 50ms ended in several failures...
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
