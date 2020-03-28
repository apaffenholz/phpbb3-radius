# A Radius login for phpbb3

This extension adds a radius login option to phpbb. Local accounts still possible and checked: If Radius returns a reject, then a login using the phpbb user table is attempted. 

# Installation

place the file tree into the ext subfolder, enable the extension in the acp customize tab, then in the general tab select Authentication and chose the radius option. Add your radius server and secret.
