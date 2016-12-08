from pymodbus.exceptions import ConnectionException
import urllib2
import time
from pymodbus.client.sync import ModbusTcpClient as ModbusClient
from datetime import datetime

  
#f = open('/home/pi/smartmeter/tempfile.txt', 'w') #create a file using the given input

# This script is started via a crontab task at 18:00. It reads the daily energy every 15 min 
# until Fronius goes offline or until 23:00.

froniusIsOnline = 1
sitePower = 0
counter = 0
while ( froniusIsOnline ):
   try:
      FroniusWR = ModbusClient(host = '192.168.1.38', port=502)
      response = FroniusWR.read_holding_registers(502,4,unit=1) # parameters: register address, number of regs to read
      sitePower = response.registers[2]                # interpret only first uint16... not really good!
 #     f.write('Actual site power is: ' + str(sitePower) + ' at %s\n'  %datetime.now())
 #     f.flush()
      if counter == 20:
          froniusIsOnline = 0   #end loop, its 20 x 15 min (5 hours) later
      else:
          counter += 1
          time.sleep(900)       # wait 15min 
#          time.sleep(10)       # wait 10sec 
   except ConnectionException:
      froniusIsOnline = 0

date = datetime.today()
urlToSet = "http://localhost/pv/web/app.php/insert/dailydata/"
urlToSet += str(date.year)
urlToSet += "-"
urlToSet += str(date.month)
urlToSet += "-"
urlToSet += str(date.day)
urlToSet += "/"
urlToSet += str(sitePower)

#f.write('Call: \"' + urlToSet + '\" ...\n')
#f.flush()

urllib2.urlopen(urlToSet)

#f.write('\nSuccessful! URL was:' + urlToSet + 'at: %s\n'  %datetime.now())
#f.close()
