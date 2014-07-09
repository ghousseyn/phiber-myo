
  Myo Phiber's Command Line Tool v0.6
  Author: Housseyn Guettaf <ghoucine@gmail.com>

 Usage:

 myo <comand> <flag> [[--option1 value][--option2 = value]...] -- arg1 arg2 ...


 Flags:

 -i	Preserve case. Files always created in lowercase if this flag is not on

 Options:

 entity		Creates an entity file or generate entities from db with -g

		 Usage:

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
		 --entity-path 		 The path to put generated files in


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

  
