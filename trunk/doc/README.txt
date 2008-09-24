				Cloud Storage API
				alpha v0.1
				README.txt
				9-22-2008

PRE-REQUISITES

Zend Framework v1.5 must be installed in a directory $ZEND_HOME.

The directory $ZEND_HOME must be part of the 'include_path' of php.ini.

INSTALLATION

Copy the files under php/* to the directory $ZEND_HOME/library/Zend/.

  cp -pR ./php/* $ZEND_HOME/library/Zend

Enter your Nirvanix account and Amazon account login information in the file text/config.php.

Copy the files under test/* to a directory (e.g. 'uapi') under the PHP 
DOCUMENT_ROOT directory, which is /var/www/html on Red Hat Linux.

  mkdir /var/www/html/uapi
  cp -pR ./test/* /var/www/html/uapi/
 
TESTING

Open a web browser with the URL http://localhost/uapi/index.php
You will see a form with three fields:

	Local File
	Target Path
	Bucket

and two buttons at the bottom:
	Nirvanix
	Amazon

When you click on the Nirvanix button, 
the information in the form will be sent to Nirvanix
and the five unified API will be executed in this order:

	listAllBuckets
	uploadContents
	downloadContents
	getMetaData
	deleteContents

Similarly when you click on the Amazon button, the
the information in the form will be sent to Amazon
and the five unified API will be executed in the same order.
