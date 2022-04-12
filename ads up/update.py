# sudo mcedit /etc/systemd/system/files_updater.service

# [Unit]
# Description=My updater service
# After=multi-user.target
# [Service]
# Type=simple
# Restart=always
# ExecStart=/usr/bin/python3 /var/www/html/update.py
# [Install]
# WantedBy=multi-user.target

# sudo systemctl daemon-reload
# sudo systemctl enable files_updater.service
# sudo systemctl start files_updater.service
# sudo systemctl status files_updater.service
# sudo systemctl restart files_updater.service

import json
import requests
from datetime import datetime
import traceback
import time


while True:
  try:
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
          resp = requests.get(links[key])
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

    time.sleep(30 * 60) # полчаса
  except:
    pass