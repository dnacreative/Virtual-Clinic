# Virtual-Clinic
This application aims to provide two main functions: 

1. Enable virtual interaction between a doctor and patient through video and text chat.
2. Hold records of all patients' case histories and corresponding treament prescribed.

### How to set it up:
1. set the variables in /include/vclinic/appvars.php to your MySQL database credentials and create the corresponding database.
2. execute *virtualclinic-structure.sql* and *virtualclinic-data.sql* (in that order) on your MySQL database.
3. copy all the files to the *htdocs* folder of your Apache server. (document root is assumed to be */htdocs/www* here.)
4. edit *webrtc-server/server.js* to set the database variables to your MySQL database credentials.
5. install and start signalling server by running `npm install` and then `node server.js`.
6. direct your browser to the server location and log in as *admin* (password is the same).
