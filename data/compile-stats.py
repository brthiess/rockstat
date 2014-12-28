HAMMER_TEAM = 1
OTHER_TEAM = 2

import MySQLdb

def isCurrentTeamHammerTeam(teamID, hammerTeamID, otherTeamID):
	if (teamID == hammerTeamID):
		return 1
	elif(teamID == otherTeamID):
		return 2
	else:
		print("Error.  Current Team is neither hammer team nor other team")

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
	teamID = teamIds[id][0]			#Get Current Team's ID
	#Arrays to hold rows for each end
	hammerEnds = []
	nonHammerEnds = []
#Get all games that the current team has played
	cur.execute("SELECT * FROM Game WHERE (Game.HammerTeamID = " + str(teamID) + " OR Game.OtherTeamID = " + str(teamID) + ")")
	allGames = cur.fetchall()
	#For each game the current team has played:
	for game in range(0, len(allGames)):
		#Get All ends with the current game ID
		cur.execute("SELECT * FROM EndScore WHERE Game = " + str(allGames[game][0]))
		gameEnds = cur.fetchall()
		#Get the Hammer Team ID and the Other Team ID
		hammerTeamID = allGames[game][2]
		otherTeamID = allGames[game][3]
		teamWithHammerThisEnd = HAMMER_TEAM
		#Returns 1 if Our Team is the team with hammer in the first end.  
		#Else returns 2 if our team is the team without hammer in the first end.
		OUR_TEAM = isCurrentTeamHammerTeam(teamID, hammerTeamID, otherTeamID)
		OPPONENT_TEAM = 3 - OUR_TEAM
		for end in range(0, len(gameEnds)):					#Iterate through each end
			hammerTeamScore = int(gameEnds[end][2])		
			otherTeamScore = int(gameEnds[end][3])
			if (teamWithHammerThisEnd == OUR_TEAM):			#Check to see if the team with hammer this end is our current team				
				if (hammerTeamScore == 0 and otherTeamScore == 0):	#Blank End
					hammerEnds.append(0)							#Append a blank
				elif (hammerTeamScore != 0):						#Else check if our team scored
					teamWithHammerThisEnd = OPPONENT_TEAM			#Flip which team has hammer
					hammerEnds.append(hammerTeamScore)
				else:
					hammerEnds.append(-otherTeamScore)				#Else the other team scored.  Append the negative amount to ours
			else:								#Else the team with hammer this end is the opposition
				if (hammerTeamScore == 0 and otherTeamScore == 0):	#Blank End
					nonHammerEnds.append(0)
				elif (hammerTeamScore != 0):						#Else Check if We Stole
					nonHammerEnds.append(hammerTeamScore)
				else:												#Else They scored with hammer
					nonHammerEnds.append(-otherTeamScore)
					teamWithHammerThisEnd = OUR_TEAM				#Flip Hammer to Our team since opponents just scored with hammer
	print("Team ID: " + str(teamID))
	print("Hammer Ends: " + str(hammerEnds))
	print("Non Hammer Ends: " + str(nonHammerEnds))
			