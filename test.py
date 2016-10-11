from pymodbus.client.sync import ModbusTcpClient as ModbusClient
import time

FroniusWR = ModbusClient(host = '192.168.1.38', port=502)

while 1:
   time.sleep(1)
   response = FroniusWR.read_holding_registers(502,4,unit=1)
   readValue = response.registers[0]
   readValue1 = response.registers[1]
   readValue2 = response.registers[2]
   readValue3 = response.registers[3]
#   FroniusWR.write_register(0,readValue)
   print(readValue)
   print(readValue1)
   print(readValue2)
   print(readValue3)
   
