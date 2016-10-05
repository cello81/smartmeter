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
   response = FroniusWR.read_input_registers(0,2)
   sitePower = response.registers[0]
except ConnectionException:
    sitePower = -1

urlToSet = "http://localhost/pv/web/app.php/insert/dailydata/"
urlToSet += str(sitePower)
urllib2.urlopen(urlToSet)

f.write('Called:' + urlToSet + 'at: %s\n'  %datetime.now())
f.close()
