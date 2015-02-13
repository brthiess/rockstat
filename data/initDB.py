def initializeTables(cur):
	cur.execute("DROP TABLE IF EXISTS ScoringFrequency")
	cur.execute("DROP TABLE IF EXISTS EndScore")
	cur.execute("DROP TABLE IF EXISTS Game")
	cur.execute("DROP TABLE IF EXISTS EndByEndAvgScoring")
	cur.execute("DROP TABLE IF EXISTS WBS")
	cur.execute("DROP TABLE IF EXISTS WPOT")	
	cur.execute("DROP TABLE IF EXISTS PlayerTeam")
	cur.execute("DROP TABLE IF EXISTS Player")
	cur.execute("DROP TABLE IF EXISTS Team")
	cur.execute("CREATE TABLE Team(\
				ID int NOT NULL AUTO_INCREMENT,\
				Games int,\
				Wins int,\
				Losses int,\
				WinPercentage float,\
				PFG float,\
				PAG float,\
				EventsPlayed int,\
				EventsWon int,\
				WinsWith int,\
				WinsWithout int,\
				LossesWith int,\
				LossesWithout int,\
				NetScoringWith float,\
				NetScoringWithout float,\
				PRIMARY KEY (ID)\
				)")	
	cur.execute("CREATE TABLE Player(\
				ID int NOT NULL AUTO_INCREMENT,\
				FirstName varchar(30),\
				LastName varchar(30),\
				Primary Key (ID)\
				)")
	cur.execute("CREATE TABLE PlayerTeam(\
				PlayerID int NOT NULL,\
				Position int, /* 1 = Lead, 2 = Second, 3 = Third 4 = Skip*/\
				TeamID int NOT NULL,\
				FOREIGN KEY (PlayerID) REFERENCES Player(ID),\
				FOREIGN KEY (TeamID) REFERENCES Team(ID)\
				)")

	cur.execute("CREATE TABLE EndByEndAvgScoring(\
				TeamID int NOT NULL,\
				EndNumber int,\
				Hammer boolean,\
				Average float,\
				Samples int,\
				FOREIGN KEY (TeamID) REFERENCES Team(ID)\
				)")

	cur.execute("CREATE TABLE Game(\
				ID int NOT NULL AUTO_INCREMENT,\
				GameDate DATE,\
				HammerTeamID int,\
				OtherTeamID int,\
				Event varchar(60),\
				FOREIGN KEY (HammerTeamID) REFERENCES Team(ID),\
				FOREIGN KEY (OtherTeamID) REFERENCES Team(ID),\
				PRIMARY KEY (ID)\
				)")

	cur.execute("CREATE TABLE EndScore(\
				EndNumber int,\
				Game int NOT NULL,\
				HammerScore int,\
				OtherScore int,\
				FOREIGN KEY (Game) REFERENCES Game(ID)\
				)")

	cur.execute("CREATE TABLE ScoringFrequency (\
				TeamID int not NULL,\
				Hammer boolean,\
				Score int,\
				rate float,\
				Samples int,\
				FOREIGN KEY (TeamID) REFERENCES Team(ID)\
				)")

	cur.execute("CREATE TABLE WPOT (\
				TeamID int not NULL,\
				WinningPercentage float,\
				MonthNumber int,\
				Samples int,\
				FOREIGN KEY (TeamID) REFERENCES Team(ID)\
				)")

	cur.execute("CREATE TABLE WBS (\
				TeamID int not NULL,\
				EndNumber int,\
				ScoreDifferential int,\
				WinningPercentage float,\
				Hammer boolean,\
				Samples int,\
				FOREIGN KEY (TeamID) REFERENCES Team(ID)\
				)")

