import json
import requests
from datetime import datetime
import traceback
import time

headers = {
  "user-agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.88 Safari/537.36"
}

links = {}
try:
  with open("/var/www/html/addons/_links.json", "r") as f:
    links = json.loads(f.read())
except:
  pass

out = "\n" + datetime.now().strftime("%d/%m/%Y, %H:%M:%S") + " update.py\n"

for key in links:
  if (links[key] != "0"):
    try:
      resp = requests.get(links[key], headers = headers)
    except:
      out += f"{key} -> {links[key]} download error =========================\n"
      tr = traceback.format_exc()
      out += f"\n{tr} \n=========================\n"
      continue

    ln = len(resp.content)
    if (ln < 1024):
      out += f"{key} -> {links[key]} < 1024, continue\n"
      continue

    out += f"{key} -> {links[key]} -> downloaded {ln} bytes\n"

    try:
      with open(f"/var/www/html/addons/{key}.file", "wb") as f:
        f.write(resp.content)
    except:
      out += f"{key} -> {links[key]} file write error =========================\n"
      tr = traceback.format_exc()
      out += f"\n{tr} \n=========================\n"

print(out)

try:
  with open(f"/var/www/html/addons/_links.log", "a") as f:
    f.write(out)
except:
  pass