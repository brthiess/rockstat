import MySQLdb

db = MySQLdb.connect(host="localhost", # your host, usually localhost
                     user="root", # your username
                      passwd="jikipol", # your password
                      db="rockstat") # name of the data base
					  
cur = db.cursor()
#Get All Team IDs
cur.execute("SELECT ID FROM TEAM")
teamIds = cur.fetchall()
#For Each Team:
for id in range(0, len(teamIds)):
	#Arrays to hold rows for each end
	hammerEnds = []
	nonHammerEnds = []
#Get all games that the current team has played
	cur.execute("SELECT * FROM Game WHERE (Game.HammerTeamID = " + str(teamIds[id][0]) + " OR Game.OtherTeamID = " + str(teamIds[id][0]) + ")"
	allGames = cur.fetchall()
	#For each game the current team has played:
	for game in range(0, len(allGames)):
		