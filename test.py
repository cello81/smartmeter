from pymodbus.client.sync import ModbusTcpClient as ModbusClient
import time

FroniusWR = ModbusClient(host = '192.168.1.38', port=502)

while 1:
   time.sleep(1)
   response = FroniusWR.read_holding_registers(500,2,unit=1)
   readValue = response.registers[0]
#   FroniusWR.write_register(0,readValue)
   print(readValue)
   
