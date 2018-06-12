# Credits based reservation system
### Overview ###
I created this system for the staff Computer Architecture and VLSI Systems laboratory of FORTH-ICS while i was working there.
We used it in the laboratory in order to reserve machines for exclusive and non-exclusive use.

Each user has 48 credits each week, which corresponds to 48 hours of exclusive use. System consumed credits only for exclusive reservations.
We schedule a cron job every Monday in order to update the credits of each user in the database.

System sends automatically e-mails to the users subscribed in the mailing list, informing them about events that are related only with exclusive reservations
such as: new, edit, cancel, change of an exclusive reservation to non-exclusive and the opposite.



### Login ###
Users can login to the system using their LDAP credentials. System stores in the database only the username of each user and the actual
matching is made with LDAP.



### Credits refund policies ###
You won't get a refund if:
1. You transfer the date of your reservation 24 hours before the actual day
2. You cancel your reservation 24 hours before the actual day
3. You change an exclusive reservation to non-exclusive 24 hours before the actual day
    
    
## Configuration
In order to make the system work you have to:
1. Import the database (res_system.sql)
2. Fill the file config.php with the credentials of the database
3. Configure the file ldap_config.php with the LDAP settings
4. Change properties in file send_email.php in order to use your mailserver 
5. Set as a cron job SQL query: **UPDATE users SET credits='48'** 

## Special thanks to
[Fullcalendar](https://github.com/fullcalendar) and [PHPMailer](https://github.com/PHPMailer/PHPMailer)
