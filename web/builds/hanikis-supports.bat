@echo off

echo ###############################################################################################
echo #                       Most Valuable Nerds Item Builder                                      #
echo #                                                                                             #
echo # Ce fichier va vous permettre de creer les sets d items recommendes pour vos champions       #
echo #                                                                                             #
echo ###############################################################################################




echo Le build suivant : 
echo 	Item 1 : Faerie Charm
echo 	Item 2 : Sight Ward
echo 	Item 3 : Health Potion
echo 	Item 4 : Ionian Boots of Lucidity
echo 	Item 5 : Heart of Gold
echo 	Item 6 : Aegis of the Legion
echo va etre affecte aux champions suivants : 
echo 	Alistar
echo 	Janna
echo 	Leona
echo 	Nunu
echo 	Sona
echo 	Soraka
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

md "Alistar"
cd "Alistar"
echo [ItemSet1] > RecItemsCLASSIC.ini
echo SetName=Set1 >> RecItemsCLASSIC.ini
echo RecItem1=1004 >> RecItemsCLASSIC.ini
echo RecItem2=2044 >> RecItemsCLASSIC.ini
echo RecItem3=2003 >> RecItemsCLASSIC.ini
echo RecItem4=3158 >> RecItemsCLASSIC.ini
echo RecItem5=3132 >> RecItemsCLASSIC.ini
echo RecItem6=3105 >> RecItemsCLASSIC.ini


cd "../"

md "Janna"
cd "Janna"
echo [ItemSet1] > RecItemsCLASSIC.ini
echo SetName=Set1 >> RecItemsCLASSIC.ini
echo RecItem1=1004 >> RecItemsCLASSIC.ini
echo RecItem2=2044 >> RecItemsCLASSIC.ini
echo RecItem3=2003 >> RecItemsCLASSIC.ini
echo RecItem4=3158 >> RecItemsCLASSIC.ini
echo RecItem5=3132 >> RecItemsCLASSIC.ini
echo RecItem6=3105 >> RecItemsCLASSIC.ini


cd "../"

md "Leona"
cd "Leona"
echo [ItemSet1] > RecItemsCLASSIC.ini
echo SetName=Set1 >> RecItemsCLASSIC.ini
echo RecItem1=1004 >> RecItemsCLASSIC.ini
echo RecItem2=2044 >> RecItemsCLASSIC.ini
echo RecItem3=2003 >> RecItemsCLASSIC.ini
echo RecItem4=3158 >> RecItemsCLASSIC.ini
echo RecItem5=3132 >> RecItemsCLASSIC.ini
echo RecItem6=3105 >> RecItemsCLASSIC.ini


cd "../"

md "Nunu"
cd "Nunu"
echo [ItemSet1] > RecItemsCLASSIC.ini
echo SetName=Set1 >> RecItemsCLASSIC.ini
echo RecItem1=1004 >> RecItemsCLASSIC.ini
echo RecItem2=2044 >> RecItemsCLASSIC.ini
echo RecItem3=2003 >> RecItemsCLASSIC.ini
echo RecItem4=3158 >> RecItemsCLASSIC.ini
echo RecItem5=3132 >> RecItemsCLASSIC.ini
echo RecItem6=3105 >> RecItemsCLASSIC.ini


cd "../"

md "Sona"
cd "Sona"
echo [ItemSet1] > RecItemsCLASSIC.ini
echo SetName=Set1 >> RecItemsCLASSIC.ini
echo RecItem1=1004 >> RecItemsCLASSIC.ini
echo RecItem2=2044 >> RecItemsCLASSIC.ini
echo RecItem3=2003 >> RecItemsCLASSIC.ini
echo RecItem4=3158 >> RecItemsCLASSIC.ini
echo RecItem5=3132 >> RecItemsCLASSIC.ini
echo RecItem6=3105 >> RecItemsCLASSIC.ini


cd "../"

md "Soraka"
cd "Soraka"
echo [ItemSet1] > RecItemsCLASSIC.ini
echo SetName=Set1 >> RecItemsCLASSIC.ini
echo RecItem1=1004 >> RecItemsCLASSIC.ini
echo RecItem2=2044 >> RecItemsCLASSIC.ini
echo RecItem3=2003 >> RecItemsCLASSIC.ini
echo RecItem4=3158 >> RecItemsCLASSIC.ini
echo RecItem5=3132 >> RecItemsCLASSIC.ini
echo RecItem6=3105 >> RecItemsCLASSIC.ini


cd "../"

Cls
echo Les fichiers ont bien ete crees, a bientot sur mvnerds.com
pause