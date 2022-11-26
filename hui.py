import csv
from operator import itemgetter

with open('records.csv', newline='') as f:
    reader = csv.reader(f)
    data = list(reader)

init_len = len(data)

result = []
decline = []
banned = []

for rec in data:
    if (rec[3] == 'ok'): result.append(rec)
    elif (rec[3] == 'decline'): decline.append(rec)
    elif (rec[3] == 'banned'): banned.append(rec)

data = []

print(f"ok: {len(result)}")
print(f"decline: {len(decline)}")
print(f"banned: {len(banned)}")

decline = sorted(decline, key=itemgetter(7))
banned = sorted(banned, key=itemgetter(7))

current_ip = ''

decline_unique = []
for rec in decline:
    if (rec[7] != current_ip):
        decline_unique.append(rec)
        current_ip = rec[7]

banned_unique = []
for rec in banned:
    if (rec[7] != current_ip):
        banned_unique.append(rec)
        current_ip = rec[7]

result += decline_unique
result += banned_unique

result = sorted(result, key=lambda x: int(x[0]))

print(f"deleted: {init_len - len(result)}")
print(f"total: {len(result)}")
print(f"last: {result[-1]}")

with open('result.csv', 'w', encoding='UTF8', newline='') as f:
    writer = csv.writer(f, quotechar='"')
    writer.writerows(result)

# USE IPNEW;

# LOAD DATA INFILE '/var/lib/mysql-files/result.csv'
# INTO TABLE `records`
# FIELDS TERMINATED BY ','
# ENCLOSED BY '"' LINES
# TERMINATED BY '\n'
# (`id`,`streamid`,`substreamid`,`type`,`reason`,`ua`,`sub`,`ip`,`distributor`,`country`,`timestamp`);

# CREATE TABLE `records_dump` AS SELECT * FROM `records`;