## Zabbix OpenVPN Template

This template reads the status file generated by the OpenVPN server and makes the discovery of connected clients: number of clients, connected since, bytes received, bytes sent and real address.

## Configuration steps

Configure the "status" parameter in your OpenVPN server config file.

For example:
```
status openvpn-server-client-status.log
```

The user running the zabbix agent must have read permission on the OpenVPN status file (usually "zabbix" user).

Configure the {$OVPN_STATUS_FILE} macro correctly so that the template can read the status file generated by the "status" parameter defined in the OpenVPN server configuration file.

Example content of the status file generated by the OpenVPN server:

```
# cat openvpn-server-client-status.log 
OpenVPN CLIENT LIST
Updated,Fri Apr  2 01:19:07 2021
Common Name,Real Address,Bytes Received,Bytes Sent,Connected Since
client1,189.28.208.115:62054,42808429,55296431,Wed Mar 31 11:46:05 2021
client2,201.183.39.92:10088,156872,3860927,Fri Apr  2 01:00:23 2021
ROUTING TABLE
Virtual Address,Common Name,Real Address,Last Ref
10.1.1.15,client2,201.183.39.92:10088,Fri Apr  2 01:19:02 2021
10.1.1.2,client1,189.28.208.115:62054,Fri Apr  2 01:19:06 2021
GLOBAL STATS
Max bcast/mcast queue length,0
END
```
