# call this script without any argument to be able to close it with a keyboard input
import urllib2
import os
import time
import sys
from datetime import datetime

  
#f = open('collectdailylogfile.txt', 'w') #create a file using the given input

date = datetime.today()
urlToSet = "http://localhost/pv/web/app.php/update/dailydata/"
urlToSet += str(date.year)
urlToSet += "-"
urlToSet += str(date.month)
urlToSet += "-"
urlToSet += str(date.day)

#f.write('Call: \"' + urlToSet + '\" ...')
#f.flush()

urllib2.urlopen(urlToSet)

#f.write('\nSuccessful! URL was:' + urlToSet + 'at: %s\n'  %datetime.now())
#f.close()
