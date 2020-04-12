# ministra-stress-test
Synthetic load test with emulation of a typical user flow:

## Installation
- The tool requires ```php7``` and ```php-curl``` libraries to be installed

## Authorization data
The tool suggests that the several steps had been followed before the test would started:
- The [licence keys](https://wiki.infomir.eu/eng/ministra-players/license-keys) have already been inserted in the Ministra database
- There is the ```users.csv``` file in the root directory of the project with the list of the users in a form of
```
login1,password1,meta1_1,meta1_2,meta1_3
login2,password2,meta2_1,meta2_2,meta2_3
...
loginN,passwordN,metaN_1,metaN_2,metaN_3
```
- The tariff plan assigned to each user of ```users.csv``` list contains [the service package to automatically issue the license key](https://wiki.infomir.eu/eng/ministra-tv-platform/administrative-panel/license-keys/how-to-configure-automatic-assignment-of-license-keys)

## Define variables
- Assign the proper value of the needed portal URL to the ```$portal``` variable at the line 5 of ```run.php``` script
- ```Userflow``` class constructor defines the default values of various variables that influence the load and behavior

## Running the test
Run ```php run.php N``` in the terminal console to launch a single process of the user behavior emulation, where N - the number of the line in the ```users.csv``` file which the authorization data should be taken from

Or it is possible to run a script to generate multiple concurrent users' sessions.
For example, join new user every 1-4 second until there are 1000 users online:

```for i in {1..1000}; do php run.php $i >> ./userflow.log & sleep $[ ( $RANDOM % 4 )  + 1 ]s; echo $i users online >> ./userflow.log; done```

## Userflow algorithm
![](https://volyanytsky-infomir.s3.eu-central-1.amazonaws.com/userflow.png)
