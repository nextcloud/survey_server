# Popularity Contest Server

This is the server part of the popularity contest app. It collects anonymised
data send by other ownCloud installations, evaluates them and visualize it.

Data needs to be send to ````http://owncloudserver.org/index.php/apps/popularitycontestserver/api/v1/data````
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
                   "ocversion" : <owncloud_version>,
                   "users" : <number_of_users>
               }
}
````

The ````id```` needs to be the same every time the ownCloud server sends a update
of his data so that the popularity contest server can detect duplicates.

At the moment the popularity contest server supports the values shown in the JSON
example above but the client can send any data he wish, in the feature the server
will be able to evaluate more data.

For testing purpose you can use curl to send some dummy values to the server:

````
curl --data "data={\"id\" : \"randomID_454353\", \"apps\" : [ \"calendar\", \"deleted_files\" ], \"system\" : { \"phpversion\" : \"5.6\", \"ocversion\" : \"8.1\", \"users\" : 22 }}" http://yourowncloudserver.org/index.php/apps/popularitycontestserver/api/v1/data
````
