
  Myo Phiber's Command Line Tool v0.6.2


 	Usage:

 	myo <comand> <flag> [[--option1 value][--option2 = value]...] -- arg1 arg2 ...


 Flags:

 	-i		Preserve case otherwise files will always be created in lowercase
 	-g		Generate entity files when used with myo entity

 Commands:

 	app		Creates a new Phiber application
        
		 Usage:
		 
		 myo app <appname>
		 
		 Options:
		 
		 --app-path		Specify the application path
		 
 	entity		Creates an entity file or generate entities from db with -g

		 Usage:
		 
		 myo entity <entity name>
		 
		 Creates an empty entity file
		 
		 

		 myo entity -g 		

		 Generates entity files from the database

		 with no options it will use your config to access the db and

		 put the files into the entity folder

		 options:

		 --db-dsn 		 The dsn of your db
		 --db-host 		 Database host
		 --db-name 		 Database name, overides --db-dsn
		 --db-user 		 Database username
		 --db-pass 		 Database password
		 --entity-path 		 The folder to put generated files in
		 


 	ext		Creates myo extensions.

  		 Usage:

  		 myo ext <extension name>

  		 The new extension will be used as a command:

  		 myo <extension name>


 	help		Provides more information about commands.

		 Usage:

		 myo help <command>


 	mvc		Creates different parts of the MVC layout.

  		 mvc <flag> [option]

  		 Options:

  		 --module <module name>

  		 Creates a module and defaults to module 'default'


  		 --controller <controller name>

  		 Creates a controller and defaults to 'index'


  		 --model <model name>

  		 Creates a model


  		 --action <action name>

  		 Creates an action for a given controller

  
