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
	TeamRank int,\
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
	#Get Ranks for each team
	for i in range(-8,9):
		print("Done One");
		#cur.execute("SELECT Team.id, COUNT(*) c FROM Team, Game WHERE Team.ID = Game.HammerTeamID OR Team.ID = Game.OtherTeamID Group by team.id having c > 30")
		cur.execute("SELECT ScoringFrequency.TeamID, ScoringFrequency.Hammer, ScoringFrequency.Score, ScoringFrequency.rate, ScoringFrequency.TeamRank FROM ScoringFrequency, Team WHERE ScoringFrequency.TeamID=Team.ID AND ScoringFrequency.Score = " + str(i) + " AND ScoringFrequency.Hammer = True AND Team.Games >= 30 ORDER BY ScoringFrequency.rate DESC");
		print("Done")
		scoringFrequenciesHammer = cur.fetchall()
		cur.execute("SELECT ScoringFrequency.TeamID, ScoringFrequency.Hammer, ScoringFrequency.Score, ScoringFrequency.rate, ScoringFrequency.TeamRank FROM ScoringFrequency, Team WHERE ScoringFrequency.TeamID=Team.ID AND ScoringFrequency.Score = " + str(i) + " AND ScoringFrequency.Hammer = False AND Team.Games >= 30 ORDER BY ScoringFrequency.rate DESC");
		print("Done")
		scoringFrequenciesNonHammer = cur.fetchall()
		for s in range(0, len(scoringFrequenciesHammer)):
			IDHammer = scoringFrequenciesHammer[s][0]
			IDNonHammer = scoringFrequenciesNonHammer[s][0]
			cur.execute("UPDATE ScoringFrequency SET TeamRank = " + str(s+1) + " WHERE TeamID = " + str(IDHammer) + " AND Hammer = True AND Score = " + str(i) + " ")
			cur.execute("UPDATE ScoringFrequency SET TeamRank = " + str(s+1) + " WHERE TeamID = " + str(IDNonHammer) + " AND Hammer = False AND Score = " + str(i) + " ")
			
			
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
	

#Connect To Database
db = MySQLdb.connect(host="localhost", # your host, usually localhost
                     user="root", # your username
                      passwd="jikipol", # your password
                      db="rockstat") # name of the data base
					  
cur = db.cursor()

print("0%")
compileNetScoring()
print("\b\b50%")
compileScoringFrequencies()
print("\b\b\b100%")
