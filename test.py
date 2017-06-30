from pymodbus.client.sync import ModbusTcpClient as ModbusClient
import time

FroniusWR = ModbusClient(host = '192.168.1.38', port=502)

while 1:
#   responseAnzahl = FroniusWR.read_holding_registers(40262+9,1,unit=1)
#   readAnzahl = responseAnzahl.registers[0]

   responseTotal = FroniusWR.read_holding_registers(500,2,unit=1)
   readValueTotal = responseTotal.registers[0]

   responseModel = FroniusWR.read_holding_registers(40263+0,1,unit=1)
   readValueModel = responseModel.registers[0]

   responseFactor = FroniusWR.read_holding_registers(40263+4,1,unit=1)
   readValueFactor = responseFactor.registers[0]

   responseOst = FroniusWR.read_holding_registers(40263+41,1,unit=1)
   readValueOst = responseOst.registers[0] / 100

   responseWest = FroniusWR.read_holding_registers(40263+21,1,unit=1)
   readValueWest = responseWest.registers[0] / 100

   print("Model:    ", readValueModel)
   print("Factor:   ", readValueFactor)
   print("Ost:      ", readValueOst)
   print("West:     ", readValueWest)
   print("Zusammen: ", readValueOst + readValueWest)
   print("Total:    ", readValueTotal)
   print("")

   time.sleep(1)

#  print("Anzahl: ", readAnzahl)

   
