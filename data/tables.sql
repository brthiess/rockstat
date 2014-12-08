CREATE TABLE Team(
ID int NOT NULL AUTO_INCREMENT,
SkipLast varchar(30),
ThirdLast varchar(30),
SecondLast varchar(30),
LeadLast varchar(30),	
SkipFirst varchar(30),
ThirdFirst varchar(30),
SecondFirst varchar(30),
LeadFirst varchar(30),	
PRIMARY KEY (ID)
);

CREATE TABLE EndScore(
EndNumber int,
Game int NOT NULL,
HammerScore int,
OtherScore int,
FOREIGN KEY (Game) REFERENCES Game(ID)
);

INSERT INTO EndScore VALUES(
1,
2,
3,
0
);
INSERT INTO EndScore VALUES(
2,
2,
3,
0
);
INSERT INTO EndScore VALUES(
3,
2,
0,
4
);
INSERT INTO EndScore VALUES(
4,
2,
0,
1
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


INSERT INTO Team VALUES(
'',
'Testskip', 'third', 
'firstsecond', 'thirdskip', 
'firstskip', 'thirdskip', 
'firstskip', 'thirdskip'
);
SELECT ID FROM Team ORDER BY id DESC LIMIT 0, 1
SELECT ID FROM Team WHERE SkipLast = 'firstskip';

INSERT INTO Game VALUES(
'',
'01-01-02',
1,
2
);
