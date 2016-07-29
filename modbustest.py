#!/usr/bin/env python
from pymodbus.client.sync import ModbusTcpClient as ModbusClient
import thread, time

def input_thread(L):
        raw_input()
        L.append(None)

def do_write_modbus():
        L = []
        thread.start_new_thread(input_thread, (L,))

        #Modbus-Slave
        RPiCODESYS = ModbusClient(host = '192.168.0.101', port=502)
        write_value = input ('Inputvalue: ')
        loop = input ('Loop starten: ')
        write_register = 0

        while 1:
                time.sleep(.1)
                if L: break
                RPiCODESYS.write_register(write_register, write_value)

do_write_modbus()
