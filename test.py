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

#   print("Model:    ", readValueModel)
#   print("Factor:   ", readValueFactor)
#   print("Ost:      ", readValueOst)
#   print("West:     ", readValueWest)
#   print("Zusammen: ", readValueOst + readValueWest)
#   print("Total:    ", readValueTotal)
#   print("")

   if readValueOst == 655:
      print("readValueOst", readValueOst)
      print("readValueFactor", readValueFactor)

   if readValueWest == 655:
      print("readValueWest", readValueWest)
      print("readValueFactor", readValueFactor)

   if readValueFactor == 0xFFFE:  # 65534
       sitePower = responseOst.registers[0] / 100 # this only interprets one uin$
   elif readValueFactor == 0x8000: # 32768
       sitePower = 0
   else:
       sitePower = responseOst.registers[0] / 10 # this only interprets one uint$



#   if readValueFactor == 0x8000:
#       print("readValueOst: ", readValueOst)
#   elif readValue
#       print("readValueWest: ", readValueWest)


   time.sleep(1)

#  print("Anzahl: ", readAnzahl)

   
