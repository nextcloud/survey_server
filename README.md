# Popularity Contest Server

This is the survey server app. It collects anonymised
data send by other Nextcloud installations, evaluates them and visualize it.

Data needs to be send to ````https://nextcloudServer.org/index.php/apps/survey_server/api/v1/data````
The data set has to be JSON encoded and needs to look like:

````
{
    "id" : <id_computed_by_sender>,
    "apps" : [
                 <app1>,
                 <app2>,
                 ...
             ],
    "system" : {
                   "phpversion" : <php_version>,
                   "ncversion" : <nextcloud_version>,
                   "users" : <number_of_users>
               }
}
````

The ````id```` needs to be the same every time the Nextcloud server sends a update
of his data so that the survey server can detect duplicates.

At the moment the survey server supports the values shown in the JSON
example above but the client can send any data he wish, in the feature the server
will be able to evaluate more data.

For testing purpose you can use curl to send some dummy values to the server:

````
curl --data "data={\"id\" : \"randomID_454353\", \"apps\" : [ \"calendar\", \"deleted_files\" ], \"system\" : { \"phpversion\" : \"5.6\", \"ncversion\" : \"9.0\", \"users\" : 22 }}" https://nextcloudServer.org/index.php/apps/survey_server/api/v1/data
````

## Contribute

All contributions beginning from July, 8 2016 are considered to be licensed under the "AGPLv3 or any later version".
