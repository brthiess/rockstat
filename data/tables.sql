DROP TABLE IF EXISTS ScoringFrequency;
DROP TABLE IF EXISTS EndScore;
DROP TABLE IF EXISTS Game;
DROP TABLE IF EXISTS EndByEndAvgScoring;
DROP TABLE IF EXISTS PlayerTeam;
DROP TABLE IF EXISTS Player;
DROP TABLE IF EXISTS Team;
CREATE TABLE Team(
ID int NOT NULL AUTO_INCREMENT,
Games int,
Wins int,
Losses int,
WinPercentage float,
PFG float,
PAG float,
EventsPlayed int,
EventsWon int,
WinsWith int,
WinsWithout int,
LossesWith int,
LossesWithout int,
NetScoringWith float,
NetScoringWithout float,
PRIMARY KEY (ID)
);

CREATE TABLE Player(
ID int NOT NULL AUTO_INCREMENT,
FirstName varchar(30),
LastName varchar(30),
Primary Key (ID)
);

CREATE TABLE PlayerTeam(
PlayerID int NOT NULL,
Position int, /* 1 = Lead, 2 = Second, 3 = Third 4 = Skip*/
TeamID int NOT NULL,
FOREIGN KEY (PlayerID) REFERENCES Player(ID),
FOREIGN KEY (TeamID) REFERENCES Team(ID)
);

CREATE TABLE EndByEndAvgScoring(
TeamID int NOT NULL,
EndNumber int,
Hammer boolean,
Average float,
FOREIGN KEY (TeamID) REFERENCES Team(ID)
);

CREATE TABLE Game(
ID int NOT NULL AUTO_INCREMENT,
GameDate DATE,
HammerTeamID int,
OtherTeamID int,
Event varchar(60),
FOREIGN KEY (HammerTeamID) REFERENCES Team(ID),
FOREIGN KEY (OtherTeamID) REFERENCES Team(ID),
PRIMARY KEY (ID)
);

CREATE TABLE EndScore(
EndNumber int,
Game int NOT NULL,
HammerScore int,
OtherScore int,
FOREIGN KEY (Game) REFERENCES Game(ID)
);

CREATE TABLE ScoringFrequency (
TeamID int not NULL,
Hammer boolean,
Score int,
rate float,
FOREIGN KEY (TeamID) REFERENCES Team(ID)
);
