from pymodbus.exceptions import ConnectionException
import urllib2
import time
from pymodbus.client.sync import ModbusTcpClient as ModbusClient
from datetime import datetime

  
#f = open('/home/pi/smartmeter/tempfile.txt', 'w') #create a file using the given input

froniusIsOnline = 1
sitePower = 0
while ( froniusIsOnline ):
   try:
      FroniusWR = ModbusClient(host = '192.168.1.38', port=502)
      response = FroniusWR.read_holding_registers(502,4,unit=1) # parameters: register address, number of regs to read
      sitePower = response.registers[2]                # interpret only first uint16... not really good!
#      f.write('Actual site power is: ' + str(sitePower) + ' at %s\n'  %datetime.now())
#      f.flush()
      time.sleep(300)
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

#f.write('Call: \"' + urlToSet + '\" ...')
#f.flush()

urllib2.urlopen(urlToSet)

#f.write('\nSuccessful! URL was:' + urlToSet + 'at: %s\n'  %datetime.now())
#f.close()
