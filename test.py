from pymodbus.client.sync import ModbusTcpClient as ModbusClient
import time

FroniusWR = ModbusClient(host = '192.168.0.101', port=502)

while 1:
   time.sleep(1)
   response = FroniusWR.read_input_registers(0,2)
   readValue = response.registers[0]
   FroniusWR.write_register(0,readValue)
   print(readValue)
   
