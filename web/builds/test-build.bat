@echo off

echo ###############################################################################################
echo #                       Most Valuable Nerds Item Builder                                      #
echo #                                                                                             #
echo # Ce fichier va vous permettre de creer les sets d items recommendes pour vos champions       #
echo #                                                                                             #
echo ###############################################################################################




echo Le build suivant : 
echo 	Item 1 : Guardian Angel
echo 	Item 2 : Doran's Ring
echo 	Item 3 : Prospector's Ring
echo 	Item 4 : Recurve Bow
echo 	Item 5 : Cloth Armor
echo 	Item 6 : Wit's End
echo va etre affecte aux champions suivants : 
echo 	Ahri
echo 	Akali
pause
@echo off

echo ###############################################################################################
echo #                       Most Valuable Nerds Item Builder                                      #
echo #                                                                                             #
echo # Ce fichier va vous permettre de creer les sets d items recommendes pour vos champions       #
echo #                                                                                             #
echo ###############################################################################################




cd "C:/Program Files/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/"
cd "C:/Program Files/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/"
cd "C:/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/"
cd "C:/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/"
cd "C:/Program Files (x86)/Riot Games/League of Legends/RADS/solutions/lol_game_client_sln/releases/"
cd "C:/Program Files (x86)/Riot/League of Legends/RADS/solutions/lol_game_client_sln/releases/"

rem Permet de se placer dans le repertoire de release le plus recent
setlocal enableextensions enabledelayedexpansion
set cmpt=0
set folder=
for /d %%i in (*) do (
	set path=%%i
	set test=!path:.= !
	set temp=
	set j=1000000000
	set mult=
	for %%x in (!test!) do (
		if %%x GTR 0 (
			set /a var=!j!*%%x
			set /a temp=!temp!+!var!
		)
		set /a j=!j!/1000
	)
	if !temp! GTR !cmpt! (
		set cmpt=!temp!
		set folder=!path!
	)
)
cd !folder!
cd "deploy/DATA"


rem Cree le repertoire des champions s il n existe pas et se place dedans
md "Characters"
cd "Characters"

md "Ahri"
cd "Ahri"
echo [ItemSet1] > RecItemsCLASSIC.ini
echo SetName=Set1 >> RecItemsCLASSIC.ini
echo RecItem1=3026 >> RecItemsCLASSIC.ini
echo RecItem2=1056 >> RecItemsCLASSIC.ini
echo RecItem3=1063 >> RecItemsCLASSIC.ini
echo RecItem4=1043 >> RecItemsCLASSIC.ini
echo RecItem5=1029 >> RecItemsCLASSIC.ini
echo RecItem6=3091 >> RecItemsCLASSIC.ini


cd "../"

md "Akali"
cd "Akali"
echo [ItemSet1] > RecItemsCLASSIC.ini
echo SetName=Set1 >> RecItemsCLASSIC.ini
echo RecItem1=3026 >> RecItemsCLASSIC.ini
echo RecItem2=1056 >> RecItemsCLASSIC.ini
echo RecItem3=1063 >> RecItemsCLASSIC.ini
echo RecItem4=1043 >> RecItemsCLASSIC.ini
echo RecItem5=1029 >> RecItemsCLASSIC.ini
echo RecItem6=3091 >> RecItemsCLASSIC.ini


cd "../"

Cls
echo Les fichiers ont bien ete crees, a bientot sur mvnerds.com
pause