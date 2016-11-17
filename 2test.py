import os
from datetime import datetime
f = open('myfile','w')
f.write('hi there\n') # python will convert \n to os.linesep
f.write('Transmit worker: Prozess ID:' + str(os.getpid()) + '\n')
f.write('Recorded at: %s\n'  %datetime.now())
f.close() # you can omit in most cases as the destructor will call it

