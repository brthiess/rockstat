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
#Is given an array of ends each with the score for the end	
def getScoringFrequencies(ends):
	frequencies = {-8: 0, -7: 0, -6: 0, -5: 0, -4: 0, -3: 0, -2: 0, -1: 0, 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6:0, 7:0, 8:0}
	if (len(ends) == 0):
		return frequencies
	for e in range(0, len(ends)):
		frequencies[ends[e]] += 1.0
	for f in range(-8, 9):
		frequencies[f] = frequencies[f]/len(ends)
	return frequencies

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
			if (teamWithHammerThisEnd == HAMMER_TEAM):
				hammerTeamScore = int(gameEnds[end][2])		
				nonHammerTeamScore = int(gameEnds[end][3])
			else:
				hammerTeamScore = int(gameEnds[end][3])		
				nonHammerTeamScore = int(gameEnds[end][2])
			#print("HammerTeamScore: " + str(hammerTeamScore))
			#print("otherTeamScore: " + str(nonHammerTeamScore))
			#print("OUR_TEAM: " + str(OUR_TEAM))
			#print("OPPONENT_TEAM: " + str(OPPONENT_TEAM))
			#print("End: " + str(end))
			#print("Team With Hammer This End: " + str(teamWithHammerThisEnd))
			#raw_input(" ")
			if (teamWithHammerThisEnd == OUR_TEAM):			#Check to see if the team with hammer this end is our current team				
				if (hammerTeamScore == 0 and nonHammerTeamScore == 0):	#Blank End
					hammerEnds.append(0)							#Append a blank
				elif (hammerTeamScore != 0):						#Else check if our team scored
					teamWithHammerThisEnd = OPPONENT_TEAM			#Flip which team has hammer
					hammerEnds.append(hammerTeamScore)
				else:
					hammerEnds.append(-nonHammerTeamScore)				#Else the other team scored.  Append the negative amount to ours
			else:								#Else the team with hammer this end is the opposition
				if (hammerTeamScore == 0 and nonHammerTeamScore == 0):	#Blank End
					nonHammerEnds.append(0)
				elif (hammerTeamScore != 0):						#Team with the hammer scored
					nonHammerEnds.append(-hammerTeamScore)
					teamWithHammerThisEnd = OUR_TEAM	
				else:												#Else we stole
					nonHammerEnds.append(nonHammerTeamScore)
								#Flip Hammer to Our team since opponents just scored with hammer
	#print("Team ID: " + str(teamID))
	#print("Hammer Ends: " + str(hammerEnds))
	#print("Non Hammer Ends: " + str(nonHammerEnds))
	
	#Get Frequencies:
	print(teamID)
	frequencies = getScoringFrequencies(hammerEnds)
	for i in range (-8,9):
		cur.execute("INSERT INTO ScoringFrequency VALUES ( " +\
		str(teamID) + ", true, " + str(i) + ", " + str(frequencies[i]) + ")") 
	frequencies = getScoringFrequencies(nonHammerEnds)
	for i in range (-8,9):
		cur.execute("INSERT INTO ScoringFrequency VALUES ( " +\
		str(teamID) + ", false, " + str(i) + ", " + str(frequencies[i]) + ")") 
db.commit()