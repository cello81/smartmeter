import RPi.GPIO as GPIO  
import os
GPIO.setmode(GPIO.BCM)  
  
# GPIO 3 and 15 set up as input. It is pulled up to stop false signals  
GPIO.setup(3 , GPIO.IN, pull_up_down=GPIO.PUD_UP)  
GPIO.setup(15, GPIO.IN, pull_up_down=GPIO.PUD_UP)

def greenTransmitEnergy(channel):
   print "gruener Taster wurde gedrueckt"
#   os.system("omxplayer burp.flac")

def yellowConsumeEnergy(channel):
   print "gelber Taster wurde gedrueckt"
#   os.system("omxplayer burp.flac")

GPIO.add_event_detect(3 , GPIO.FALLING, callback=greenTransmitEnergy, bouncetime=200)
GPIO.add_event_detect(15, GPIO.FALLING, callback=yellowConsumeEnergy, bouncetime=200)

try:
   raw_input("Waiting for falling edge on port 3 and 15\nPress Enter to exit\n")
except KeyboardInterrupt:  
    GPIO.cleanup()       # clean up GPIO on CTRL+C exit  
GPIO.cleanup()           #
