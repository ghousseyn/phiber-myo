
  Myo Phiber's Command Line Tool v0.6


 Usage:

 myo <comand> <flag> [[--option1 value][--option2 = value]...] -- arg1 arg2 ...


 Options:

 entity		Creates an entity file. The entity represents a table name.

		 Usage:

		 myo entity <entityname>


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

  		 Creates an action for a controller

  
