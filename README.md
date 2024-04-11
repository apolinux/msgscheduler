## Message Scheduler

Dispatchs messages scheduled to specific day and hour.

The application allows to program delivery of messages:
- daily
- weekly each x weekday
- each x days
- at specific hour and minute

The dispatcher is user-defined.

The dispatcher allows to define :
- fixed text
- fixed message obtained from another table 
- get variable message obtained from other table as a message list

### TESTING

To run tests read phpunit.xml and modify DB_USER, DB_PASSWD and DB_DSN according to your needs.

### REQUIREMENTS

This app requires connection to PDO compatible Database like LiteSQL or MySQL.

### SCHEDULER
In bin directory there is a example script that calls the scheduler.


The scheduler must run each hour, depending on platform the command varies. 
In Linux environments
add this lines to crontab, using command

crontab -e

``
  \# runs dispatcher each hour

  0 * * * * PATH_BASE_APP/bin/dispatcher.php &> /dev/null
``
## TODO

- Parametrize messages table