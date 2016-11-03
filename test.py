from pymodbus.client.sync import ModbusTcpClient as ModbusClient
import time

FroniusWR = ModbusClient(host = '192.168.1.38', port=502)

while 1:
   time.sleep(1)
#   responseWest = FroniusWR.read_holding_registers(502,4,unit=1)
   responseWest = FroniusWR.read_holding_registers(40262+22,1,unit=1)
   responseOst = FroniusWR.read_holding_registers(40262+42,1,unit=1)
   readValue = responseWest.registers[0]
#   readValue1 = responseWest.registers[1]
#   readValue2 = responseWest.registers[2]
#   readValue3 = responseWest.registers[3]
   readValueOst = responseOst.registers[0]
#   readValueOst1 = responseOst.registers[1]
#   readValueOst2 = responseOst.registers[2]
#   readValueOst3 = responseOst.registers[3]
#   FroniusWR.write_register(0,readValue)
   print("West: ", readValue)
   print("Ost:  ", readValueOst)
#   print(readValue1)
#   print(readValue2)
#   print(readValue3)
#   print(readValueOst)
#   print(readValueOst1)
#   print(readValueOst2)
#   print(readValueOst3)
   
