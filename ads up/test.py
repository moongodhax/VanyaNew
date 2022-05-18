import requests
import os 
dir_path = os.path.dirname(os.path.realpath(__file__))

with open(dir_path + "/test.txt", 'rb') as _file:
  file = {'uploadfile': _file.read()}

re = requests.post("http://203.159.80.49/up.php?stream=TEST", files=file, timeout=120)
a = 1