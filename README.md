# OTA Backend
To use with my OTA App (or any other OTA App that can use this formatting of the output data)

Builds should go into the builds/ directory. Make sure they have a the build.prop stored inside system/build.prop, else the Server won't recognize the package

##URL Mapping  

```/api``` => All ROMs in JSON format  
```/api/<codename>``` => All ROMs for the specified device in JSON format  
```/``` => All ROMs sorted for Downloading  
```/<codename>``` => All ROMs the specified device sorted for Downloading 

##Contribute

You can find issues and submit them in the Issue Tracker or (if you feel like you can improve this program) fork this project on GitHub and drop me a Pull Request and I will get the changes merged after a review

##License

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
