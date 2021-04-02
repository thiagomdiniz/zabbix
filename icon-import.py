#!/usr/bin/python3

# Dependencies:
#  pip3 install zabbix-api

import os, base64
from zabbix_api import ZabbixAPI

zabbix_server = "https://192.168.122.67/"
zabbix_user = "Admin"
zabbix_password = "zabbix"
icons_path = '/home/user/Zabbix-Icons-V2/icons/'

zapi = ZabbixAPI(server=zabbix_server, validate_certs=False)
zapi.login(zabbix_user, zabbix_password)

def get_base64_encoded_image(image_path):
    with open(image_path, "rb") as img_file:
        return base64.b64encode(img_file.read()).decode('utf-8')


for entry in os.listdir(icons_path):
    #if os.path.isfile(os.path.join(icons_path, entry)):
    if entry.lower().endswith(('.png', '.jpg', '.jpeg')):
        print(entry)

        try:
            zapi.image.create({"name":os.path.splitext(entry)[0], "imagetype":1, "image":get_base64_encoded_image(os.path.join(icons_path, entry))})
        except:
            print("Icon already exists.")

zapi.logout()
