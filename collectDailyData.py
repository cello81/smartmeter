# call this script without any argument to be able to close it with a keyboard input
from pymodbus.exceptions import ConnectionException
from multiprocessing import Process
import urllib2
import os
import time
import threading
import sys
from pymodbus.client.sync import ModbusTcpClient as ModbusClient
from datetime import datetime

  
f = open('collectdailylogfile.txt', 'w') #create a file using the given input

try:
   FroniusWR = ModbusClient(host = '192.168.1.38', port=502)
   response = FroniusWR.read_input_registers(502,4) # parameters: register address, number of regs to read
   sitePower = response.registers[0]                # interpret only first uint16... not really good!
except ConnectionException:
   sitePower = 0

date = datetime.today()
urlToSet = "http://localhost/pv/web/app.php/insert/dailydata/"
urlToSet += str(date.year)
urlToSet += "-"
urlToSet += str(date.month)
urlToSet += "-"
urlToSet += str(date.day)
urlToSet += "/"
urlToSet += str(sitePower)

f.write('Call: \"' + urlToSet + '\" ...')
f.flush()

urllib2.urlopen(urlToSet)

f.write('\nSuccessful! URL was:' + urlToSet + 'at: %s\n'  %datetime.now())
f.close()
