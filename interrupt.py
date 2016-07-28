import RPi.GPIO as GPIO
from multiprocessing import Process
import urllib2

GPIO.setmode(GPIO.BCM)  
  
# GPIO 3 and 15 set up as input. It is pulled up to stop false signals  
GPIO.setup(3 , GPIO.IN, pull_up_down=GPIO.PUD_UP)  
GPIO.setup(15, GPIO.IN, pull_up_down=GPIO.PUD_UP)

def TransmitWorker():
   print "gruener Taster wurde gedrueckt"
   print('Prozess ID:' , os.getpid())
   urllib2.urlopen("http://localhost/pv/web/app_dev.php/insert/meterdata/36/-10/5.9")
   return 1

def ConsumeWorker():
   print "gelber Taster wurde gedrueckt"
   print('Prozess ID:', os.getpid())
   urllib2.urlopen("http://localhost/pv/web/app_dev.php/insert/meterdata/0/10/5,9")
   return 0

def greenTransmitEnergy(channel):
   TransmitProcess = Process(target=ConsumeWorker)
   TransmitProcess.start()

def yellowConsumeEnergy(channel):
   ConsumeProcess = Process(target=TransmitWorker)
   ConsumeProcess.start()

if __name__ == '__main__':
    GPIO.add_event_detect(3 , GPIO.FALLING, callback=greenTransmitEnergy, bouncetime=200)
    GPIO.add_event_detect(15, GPIO.FALLING, callback=yellowConsumeEnergy, bouncetime=200)
 
try:
   print('Prozess ID:', os.getpid())
   raw_input("\nWaiting for falling edge on port 3 and 15\nPress Enter to exit\n")
except KeyboardInterrupt:  
    GPIO.cleanup()       # clean up GPIO on CTRL+C exit  
GPIO.cleanup()           #
