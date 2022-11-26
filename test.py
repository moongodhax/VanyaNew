import requests

headers = {
  'user-agent': '1'
}

flag = True
while (flag):
  resp = requests.get('http://171.22.30.106/library.php', headers = headers)
  if (resp.text == '0'):
    print(len(resp.text))
    flag = False