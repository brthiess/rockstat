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

def compileScoringFrequencies():
	cur.execute("DROP TABLE IF EXISTS ScoringFrequency")
	cur.execute("CREATE TABLE ScoringFrequency (\
	TeamID int not NULL,\
	Hammer boolean,\
	Score int,\
	rate float,\
	FOREIGN KEY (TeamID) REFERENCES Team(ID)\
	)")
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
		#Get Net Scoring Per End With and Without Hammer
		try:
			netHammerAvg = sum(hammerEnds)*1.0/len(hammerEnds)
		except:
			netHammerAvg = 0
		try:
			netNonHammerAvg = sum(nonHammerEnds)*1.0/len(nonHammerEnds)
		except:
			netNonHammerAvg = 0
		cur.execute("UPDATE Team SET NetScoringWith=" + str(netHammerAvg) + ", NetScoringWithout=" + str(netNonHammerAvg) + " WHERE ID=" + str(teamID))
		#Get Frequencies:
		print(teamID)
		frequencies = getScoringFrequencies(hammerEnds)
		for i in range (-8,9):
			cur.execute("INSERT INTO ScoringFrequency VALUES ( " +\
			str(teamID) + ", true, " + str(i) + ", " + str(frequencies[i]) + ", -1)") 
		frequencies = getScoringFrequencies(nonHammerEnds)
		for i in range (-8,9):
			cur.execute("INSERT INTO ScoringFrequency VALUES ( " +\
			str(teamID) + ", false, " + str(i) + ", " + str(frequencies[i]) + ", -1)") 
	cur.execute("ALTER TABLE scoringfrequency ORDER BY Score, Hammer, Rate desc")
	db.commit()
	

class TeamStats:

	def __init__(self, id, games=0, wins=0, losses=0, tpf=0, tpa=0, eventsWon=0, winsWith=0, winsWithout=0, lossesWith=0, lossesWithout=0):
		self.id = id
		self.games = games
		self.wins = wins
		self.losses = losses 
		self.tpf = tpf
		self.tpa = tpa 
		self.eventsPlayed = []
		self.eventsWon = eventsWon
		self.winsWith = winsWith
		self.winsWithout = winsWithout 
		self.lossesWith = lossesWith
		self.lossesWithout = lossesWithout 
		if (self.games > 0):
			self.winPercentage = self.wins*1.0/self.games
			self.pfg = self.tpf*1.0/self.games
			self.pag = self.tpa*1.0/self.games
		else:
			self.winPercentage = 0
			self.pfg = 0
			self.pag = 0
		
	def appendEvent(self, event):
		self.eventsPlayed.append(event)
		self.eventsPlayed = list(set(self.eventsPlayed))	#Removes Duplicate Entries


#Is given a game from sql table and returns the stats for that game in python dictionary form
def getGameStats(g):
	hammerScore = 0
	otherScore = 0
	gameID = g[0]
	hammerID = g[2]
	otherID = g[3]
	event = g[4]
	cur.execute("SELECT * FROM EndScore Where Game=" + str(gameID) + " ORDER BY EndNumber ASC")
	ends = cur.fetchall()
	winnerID = -1
	loserID = -1
	winnerTPF = -1
	winnerTPA = -1
	#Tally Up Score
	for e in ends:
		hammerScore += e[2]
		otherScore += e[3]
	#Determine who won
	if (hammerScore > otherScore):
		winnerTPF = hammerScore
		winnerTPA = otherScore
		winnerID = hammerID
		loserID = otherID
	elif(hammerScore < otherScore):
		winnerTPA = hammerScore
		winnerTPF = otherScore
		winnerID = otherID
		loserID = hammerID
	else:
		cur.execute("SELECT * FROM EndScore Where Game=" + str(gameID) + " ORDER BY EndNumber ASC")
		ends = cur.fetchall()
		return None
	#Create Dictionary
	return {'winnerID': winnerID, 'loserID': loserID, 'winnerTPF': winnerTPF, 'winnerTPA': winnerTPA, 'loserTPF': winnerTPA, 'loserTPA': winnerTPF, 'event': event} 
	
#Compiles all the stats from every curling game 
def compileNetScoring():
	#Initialize Dictionary full of empty stats for each team
	teamStats = {}
	#Get number of teams and all their respective ID's
	cur.execute("SELECT ID FROM Team")
	allTeams = cur.fetchall()
	#Iterate through each team and add them to teamStats dictionary/array
	for id in allTeams:
		teamStats[int(id[0])] = TeamStats(int(id[0]))		#Add teamStats object with team id as key
	#Get All Games
	cur.execute("SELECT * FROM Game")
	allGames = cur.fetchall()
	numGames = len(allGames)
	#iterate through each game
	for g in range(0, numGames):
		#Get winner and loser for each game
		gameStats = getGameStats(allGames[g])
		#Error in game statistics.  Skip it and move on
		if (gameStats == None):
			continue
		#Append win (and wins with or without) to winner and loss to loser, add events played, tpf, tpa
		winnerID = gameStats['winnerID']
		loserID = gameStats['loserID']	
		
		hammerID = allGames[g][2]
		otherID = allGames[g][3]
		
		teamStats[winnerID].games += 1
		teamStats[winnerID].wins += 1
		teamStats[winnerID].tpf += gameStats['winnerTPF']
		teamStats[winnerID].tpa += gameStats['winnerTPA']
		teamStats[winnerID].appendEvent(gameStats['event'])
		
		if (winnerID == hammerID):
			teamStats[winnerID].winsWith += 1
			teamStats[loserID].lossesWithout += 1
		else:
			teamStats[winnerID].winsWithout += 1
			teamStats[loserID].lossesWith += 1
		
		teamStats[loserID].games += 1
		teamStats[loserID].losses += 1
		teamStats[loserID].tpf += gameStats['loserTPF']
		teamStats[loserID].tpa += gameStats['loserTPA']
		teamStats[loserID].appendEvent(gameStats['event'])
	
	for t in teamStats:
		team = TeamStats(teamStats[t].id, teamStats[t].games, teamStats[t].wins, teamStats[t].losses, teamStats[t].tpf, teamStats[t].tpa, teamStats[t].eventsWon, teamStats[t].winsWith, teamStats[t].winsWithout, teamStats[t].lossesWith, teamStats[t].lossesWithout)
		
		cur.execute("UPDATE Team SET \
					Games=" + str(team.games) + ",\
					Wins=" + str(team.wins) +", \
					Losses=" + str(team.losses) +", \
					WinPercentage=" + str(team.winPercentage) +", \
					PFG=" + str(team.pfg) +", \
					PAG=" + str(team.pag) +", \
					EventsPlayed=" + str(0) +", \
					EventsWon=" + str(0) +", \
					WinsWith=" + str(team.winsWith) +", \
					WinsWithout=" + str(team.winsWithout) +", \
					LossesWith=" + str(team.lossesWith) +", \
					LossesWithout=" + str(team.lossesWithout) +" \
					WHERE Team.ID = " + str(team.id) +" \
					")
	db.commit()
	
#Compiles Stats for End By End (EBE) Scoring Averages
def compileEBE():
	#Get Teams ID's
	cur.execute("SELECT ID FROM TEAM")
	teamIds = cur.fetchall()
	#For Each Team:
	for id in range(0, len(teamIds)):
		teamID = teamIds[id][0]			#Get Current Team's ID
		#Arrays to hold rows for each end
		hammerEnds = []
		nonHammerEnds = []
		for x in range(0, 12):
			hammerEnds.append([])
			nonHammerEnds.append([])
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
					
				if (teamWithHammerThisEnd == OUR_TEAM):			#Check to see if the team with hammer this end is our current team				
					if (hammerTeamScore == 0 and nonHammerTeamScore == 0):	#Blank End
						hammerEnds[end].append(0)							#Append a blank
					elif (hammerTeamScore != 0):						#Else check if our team scored
						teamWithHammerThisEnd = OPPONENT_TEAM			#Flip which team has hammer
						hammerEnds[end].append(hammerTeamScore)
					else:
						hammerEnds[end].append(-nonHammerTeamScore)				#Else the other team scored.  Append the negative amount to ours
				else:								#Else the team with hammer this end is the opposition
					if (hammerTeamScore == 0 and nonHammerTeamScore == 0):	#Blank End
						nonHammerEnds[end].append(0)
					elif (hammerTeamScore != 0):						#Team with the hammer scored
						nonHammerEnds[end].append(-hammerTeamScore)
						teamWithHammerThisEnd = OUR_TEAM	
					else:												#Else we stole
						nonHammerEnds[end].append(nonHammerTeamScore)
		#Get Averages for each end
		averageForEnd = []
		for h in range(0, len(hammerEnds)):		#Ends with Hammer
			try:
				averageForEnd.append(sum(hammerEnds[h])*1.0/len(hammerEnds[h]))
			except:
				averageForEnd.append(0)
		for endNumber in range(0, len(hammerEnds)):
			cur.execute("INSERT INTO EndByEndAvgScoring VALUES ( " +\
						str(teamID) + ", " + \
						str(endNumber) + ", " +\
						"True, " +\
						str(averageForEnd[endNumber]) +\
						")")
		averageForEnd = []
		for h in range(0, len(nonHammerEnds)):		#Ends without Hammer
			try: 
				averageForEnd.append(sum(nonHammerEnds[h])*1.0/len(nonHammerEnds[h]))
			except:
				averageForEnd.append(0)
		for endNumber in range(0, len(nonHammerEnds)):
			cur.execute("INSERT INTO EndByEndAvgScoring VALUES ( " +\
						str(teamID) + ", " + \
						str(endNumber) + ", " +\
						"False, " +\
						str(averageForEnd[endNumber]) +\
						")")
						
						
	db.commit()
	
def compileWPOT():
	#Get Team IDs
	cur.execute("SELECT ID FROM TEAM")
	teamIds = cur.fetchall()
	#For Each Team ID 
	for t in range(0, len(teamIds)):
		teamID = teamIds[t][0]
		#Get All of the games played by this team by each month
		for m in range(1,13):
			cur.execute("SELECT * FROM Game WHERE (HammerTeamID = " + str(teamID) +\
					" OR OtherTeamID = " + str(teamID) +\
					") AND MONTH(GameDate) = " + str(m) +\
					"")
			#Get All games for current month
			games = cur.fetchall();
			totalGames = 0
			winningGames = 0
			losingGames = 0
			#Get Winning percentage for this month
			for g in games:
				gameStats = getGameStats(g)
				if (gameStats == None):
					continue
				totalGames += 1
				if (teamID == gameStats["winnerID"]):
					winningGames += 1
				elif (teamID == gameStats["loserID"]):
					losingGames += 1
			#Make sure everything adds up or else something is wrong
			assert(totalGames == (losingGames + winningGames))

			try:
				winningPercentage = winningGames*1.0/totalGames
			except:
				#If team has not played any games this month, then set winning % to 0
				winningPercentage = 0
			#Insert winning percentage into sql db
			cur.execute("INSERT INTO WPOT VALUES(" + str(teamID) +\
						", " + str(winningPercentage) +\
						", " + str(m) +\
						")")
	db.commit()
			
def compileWinsBySituation():
	#Get All Team ID's
	cur.execute("SELECT ID FROM Team");
	teamIds = cur.fetchall()
	#Iterate Through Each Team:
	for t in range(0, len(teamIds)):
		teamID = teamIds[t][0]
		print("Team ID: " + str(teamID))
		#Initialize triple-dimensional array to this:
		#hammerEnds[EndNumber][Score Differential (From Down 4 to Up 4)][Win/Loss Data (ex: 0,1,1,1,1,0...)]
		hammerEnds = {}
		nonHammerEnds = {}
		for end in range(0,13):
			hammerEnds[end] = {}
			nonHammerEnds[end] = {}
			for scoreDifferential in range(-4,5):
				hammerEnds[end][scoreDifferential] = []
				nonHammerEnds[end][scoreDifferential] = []
		#Get all games that team has played
		cur.execute("SELECT * FROM Game WHERE HammerTeamID = " + str(teamID) + " OR OtherTeamID = " + str(teamID))
		games = cur.fetchall()
		for g in games:
			#Get the game stats for the game:
			gameStats = getGameStats(g)
			if (gameStats == None):
				continue
			#Get ends for the current game
			cur.execute("SELECT * FROM EndScore WHERE Game = " + str(g[0]) + " ORDER BY EndNumber Asc")
			gameEnds = cur.fetchall()
			scoreDifferential = 0
			hammerTeamID = g[2]
			otherTeamID = g[3]
			teamWithHammerThisEnd = HAMMER_TEAM
			OUR_TEAM = isCurrentTeamHammerTeam(teamID, hammerTeamID, otherTeamID)
			OPPONENT_TEAM = 3 - OUR_TEAM
			#print("\n\n\nGame Ends: " + str(gameEnds))
			for end in range(0, len(gameEnds)):					#Iterate through each end
				#print("\nEnd Number: " + str(gameEnds[end][0]))
				
				if (teamWithHammerThisEnd == HAMMER_TEAM):
					hammerTeamScore = int(gameEnds[end][2])		
					nonHammerTeamScore = int(gameEnds[end][3])
				else:
					hammerTeamScore = int(gameEnds[end][3])		
					nonHammerTeamScore = int(gameEnds[end][2])
				
				#print("HammerTeamScore: " + str(hammerTeamScore))
				#print("NonHammerTeamScore: " + str(nonHammerTeamScore))
				#print("TeamWithHammerThisEnd: " + str(teamWithHammerThisEnd))
				#print("ScoreDifferential: " + str(scoreDifferential))
				if (teamWithHammerThisEnd == OUR_TEAM):			#Check to see if the team with hammer this end is our current team				
					if (gameStats["winnerID"] == teamID):			#If the current team has hammer and won the game then append a win to the hammer array
						
						if (scoreDifferential > 4):
							#print("Appended Win For Up 4 With Hammer")
							hammerEnds[gameEnds[end][0]][4].append(1)
						elif (scoreDifferential < -4):
							#print("Appended Win For Down 4 With Hammer")
							hammerEnds[gameEnds[end][0]][-4].append(1)
						else:
							#print("Appended Win For In Between With Hammer")
							hammerEnds[gameEnds[end][0]][scoreDifferential].append(1)	
						if (hammerTeamScore > 0):					#Switch the team who has hammer
							teamWithHammerThisEnd = OPPONENT_TEAM
						scoreDifferential = scoreDifferential + hammerTeamScore - nonHammerTeamScore
					else:
						if (scoreDifferential > 4):
							hammerEnds[gameEnds[end][0]][4].append(0)
						elif (scoreDifferential < -4):
							hammerEnds[gameEnds[end][0]][-4].append(0)
						else:
							#print("Appended Loss For " + str(scoreDifferential) +" With Hammer")
							hammerEnds[gameEnds[end][0]][scoreDifferential].append(0)
						if (hammerTeamScore > 0):
							teamWithHammerThisEnd = OPPONENT_TEAM
						scoreDifferential = scoreDifferential + hammerTeamScore - nonHammerTeamScore
				else:								#Else the team with hammer this end is the opposition
					if (gameStats["winnerID"] == teamID):			#If the current team does not have hammer and won the game then append a win to the nonhammer array
						if (scoreDifferential > 4):
							nonHammerEnds[gameEnds[end][0]][4].append(1)
						elif (scoreDifferential < -4):
							nonHammerEnds[gameEnds[end][0]][-4].append(1)
						else:	
							#print("Appended Win For In Between With Hammer")
							nonHammerEnds[gameEnds[end][0]][scoreDifferential].append(1)
						if (hammerTeamScore > 0):
							teamWithHammerThisEnd = OUR_TEAM
						scoreDifferential = scoreDifferential + nonHammerTeamScore - hammerTeamScore
					else:
						if (scoreDifferential > 4):
							nonHammerEnds[gameEnds[end][0]][4].append(0)
						elif (scoreDifferential < -4):
							nonHammerEnds[gameEnds[end][0]][-4].append(0)
						else:
							#print("Appended Loss For " + str(scoreDifferential) +" With Hammer")
							nonHammerEnds[gameEnds[end][0]][scoreDifferential].append(0)	
						if (hammerTeamScore > 0):
							teamWithHammerThisEnd = OUR_TEAM
						scoreDifferential = scoreDifferential + nonHammerTeamScore - hammerTeamScore
		for e in range(0,12):
			for sd in range(-4,5):
				try:
					hammerEnds[e][sd] = sum(hammerEnds[e][sd])*1.0/len(hammerEnds[e][sd])
				except:
					hammerEnds[e][sd] = 0
				try:
					nonHammerEnds[e][sd] = sum(nonHammerEnds[e][sd])*1.0/len(nonHammerEnds[e][sd])
				except:
					nonHammerEnds[e][sd] = 0
		cur.execute("INSERT INTO WBS VALUES(
	#Connect To Database
db = MySQLdb.connect(host="localhost", # your host, usually localhost
                     user="root", # your username
                      passwd="jikipol", # your password
                      db="rockstat") # name of the data base
					  
cur = db.cursor()


#compileNetScoring()
#compileScoringFrequencies()
#compileEBE()
#compileWPOT()
compileWinsBySituation()



