import MySQLdb
import wctCrawler
import sys
import initDB
import compileStats


#Run Web Crawler to Update Stats
if ('crawl' == sys.argv[1]):
	updateGames()		#Crawl the web for new games

	

	
db = MySQLdb.connect(host="localhost", # your host, usually localhost
                     user="root", # your username
                      passwd="jikipol", # your password
                      db="rockstat") # name of the data base
					  
cur = db.cursor()

print("Initializing Database")
initDB.initializeTables(cur)

f = open('games.dat', 'r')	
#Put files into an array
games_dat = f.readlines()
#Close file
f.close()
#strip each entry in the array of the \n
games_dat = [x.strip('\n') for x in games_dat]

hammer_skip_first=''
hammer_third_first=''
hammer_second_first=''
hammer_lead_first=''
hammer_skip_last=''
hammer_third_last=''
hammer_second_last=''
hammer_lead_last=''

other_skip_first=''
other_third_first=''
other_second_first=''
other_lead_first=''
other_skip_last=''
other_third_last=''
other_second_last=''
other_lead_last=''

hammer_linescore=[]
other_linescore=[]

game_date=''
event=''
#Read data from text file and place it in sql tables
for g in range(0, len(games_dat)):
	if(g % 5000 == 0):
		print("Games Analysed: " + str(g/26))
	if ('_d' in games_dat[g]):
		game_date=games_dat[g+1]
	elif('_lh' in games_dat[g]):
		linescore = games_dat[g+1].split("'")
		for l in range(0, len(linescore)):
			if (l%2==1):
				hammer_linescore.append(linescore[l])
	elif('_lo' in games_dat[g]):
		linescore = games_dat[g+1].split("'")
		for l in range(0, len(linescore)):
			if (l%2==1):
				other_linescore.append(linescore[l])
		#Weird bug in games.dat needed to be fixed here
		if (len(other_linescore) >= 12 and len(hammer_linescore) == 0):		
			for otli in range(0, len(other_linescore)):
				if (otli >= len(other_linescore)/2):
					hammer_linescore.append(other_linescore[otli])
			i = len(other_linescore)
			other_linescore_length = len(other_linescore)/2
			while(i > other_linescore_length):
				other_linescore.pop()
				i -= 1
	elif('_hl' in games_dat[g]):
		name = games_dat[g+1].split("'")
		try:
			hammer_lead_last = name[3]
		except:
			hammer_lead_last = 'Unknown'
		try:
			hammer_lead_first = name[1]
		except:
			hammer_lead_first = 'Unknown'
	elif('_hs' in games_dat[g]):
		name = games_dat[g+1].split("'")
		try:
			hammer_second_last = name[3]
		except:
			hammer_second_last = 'Unknown'
		try:
			hammer_second_first = name[1]
		except:
			hammer_second_first = 'Unknown'
	elif('_ht' in games_dat[g]):
		name = games_dat[g+1].split("'")
		if ('_hl' in name):
			continue
		else:
			try:
				hammer_third_last = name[3]
			except:
				hammer_third_last = 'Unknown'
			try:
				hammer_third_first = name[1]
			except:
				hammer_third_first = 'Unknown'
	elif('_hf' in games_dat[g]):
		name = games_dat[g+1].split("'")
		try:
			hammer_skip_last = name[3]
		except:
			hammer_skip_last = 'Unknown'
		try:
			hammer_skip_first = name[1]
		except:
			hammer_skip_first = 'Unknown'
	elif('_ol' in games_dat[g]):
		name = games_dat[g+1].split("'")
		try:
			other_lead_last = name[3]
		except:
			other_lead_last = 'Unknown'
		try:
			other_lead_first = name[1]
		except:
			other_lead_first = 'Unknown'
	elif('_os' in games_dat[g]):
		name = games_dat[g+1].split("'")
		try:
			other_second_last = name[3]
		except:
			other_second_last = 'Unknown'
		try:
			other_second_first = name[1]
		except:
			other_second_first = 'Unknown'
	elif('_ot' in games_dat[g]):
		name = games_dat[g+1].split("'")
		if ('_ol' in name):
			continue
		else:
			try:
				other_third_last = name[3]
			except:
				other_third_last = "Unknown"
			try:
				other_third_first = name[1]
			except:
				other_third_first = 'Unknown'
	elif('_of' in games_dat[g]):
		name = games_dat[g+1].split("'")
		try:
			other_skip_last = name[3]
		except:
			other_skip_last = 'Unknown'
		try:
			other_skip_first = name[1]
		except:
			other_skip_first = 'Unknown'
	elif('_e' in games_dat[g]):
		if (len(other_linescore) != len(hammer_linescore)):
			print("Error")
			print(other_linescore)
			print(hammer_linescore)
			hammer_linescore = []
			other_linescore = []
			continue
		event = games_dat[g+1].split("'")
		game_event = event[0]
		
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + hammer_skip_last + "%' AND FirstName LIKE '%" + hammer_skip_first + "%'")
		#If no player found with this name exists, create a new db row
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						(NULL, '" + hammer_skip_first + "', '" + hammer_skip_last + "')")
			cur.execute("SELECT LAST_INSERT_ID()")
			hammerSkipID = int(cur.fetchall()[0][0])	
		else:
			hammerSkipID = int(cur.fetchall()[0][0])
						
		#Third
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + hammer_third_last + "%' AND FirstName LIKE '%" + hammer_third_first + "%'")											
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						(NULL,'" + hammer_third_first + "', '" + hammer_third_last + "')")
			cur.execute("SELECT LAST_INSERT_ID()")
			hammerThirdID = int(cur.fetchall()[0][0])
		else:
			hammerThirdID = int(cur.fetchall()[0][0])
		#Second	
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + hammer_second_last + "%' AND FirstName LIKE '%" + hammer_second_first + "%'")													
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						(NULL, '" + hammer_second_first + "', '" + hammer_second_last + "')")
			cur.execute("SELECT LAST_INSERT_ID()")
			hammerSecondID = int(cur.fetchall()[0][0])				
		else:
			hammerSecondID = int(cur.fetchall()[0][0])

			
		#Lead
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + hammer_lead_last + "%' AND FirstName LIKE '%" + hammer_lead_first + "%'")								
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						(NULL, '" + hammer_lead_first + "', '" + hammer_lead_last + "')")
			cur.execute("SELECT LAST_INSERT_ID()")
			hammerLeadID = int(cur.fetchall()[0][0])		
		else:
			hammerLeadID = int(cur.fetchall()[0][0])
		
		#Other Skip
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + other_skip_last + "%' AND FirstName LIKE '%" + other_skip_first + "%'")
									
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						(NULL, '" + other_skip_first + "', '" + other_skip_last + "')")
			cur.execute("SELECT LAST_INSERT_ID()")
			otherSkipID = int(cur.fetchall()[0][0])		
		else:
			otherSkipID = int(cur.fetchall()[0][0])
			
			
		#Other Third	
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + other_third_last + "%' AND FirstName LIKE '%" + other_third_first + "%'")
												
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						(NULL, '" + other_third_first + "', '" + other_third_last + "')")
			cur.execute("SELECT LAST_INSERT_ID()")
			otherThirdID = int(cur.fetchall()[0][0])		
		else:
			otherThirdID = int(cur.fetchall()[0][0])
		#Other Second
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + other_second_last + "%' AND FirstName LIKE '%" + other_second_first + "%'")
												
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						(NULL, '" + other_second_first + "', '" + other_second_last + "')")
			cur.execute("SELECT LAST_INSERT_ID()")
			otherSecondID = int(cur.fetchall()[0][0])		
		else:
			otherSecondID = int(cur.fetchall()[0][0])
		#Other Lead
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + other_lead_last + "%' AND FirstName LIKE '%" + other_lead_first + "%'")											
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						(NULL, '" + other_lead_first + "', '" + other_lead_last + "')")
			cur.execute("SELECT LAST_INSERT_ID()")
			otherLeadID = int(cur.fetchall()[0][0])					
		else:
			otherLeadID = int(cur.fetchall()[0][0])
						
								
		#All data for a game has been obtained.
		#Put data from the game into sql
		#Check to make sure team is not a duplicate
		numrows = cur.execute("SELECT TeamID FROM PlayerTeam WHERE (PlayerID = " + str(hammerSkipID) + " OR PlayerID = " + str(hammerThirdID) + " \
					OR PlayerID = " + str(hammerSecondID) + " \
					OR PlayerID = " + str(hammerLeadID) \
					+ ") GROUP BY TeamID HAVING count(*) = 4")
		#Team already exists.  
		if (numrows > 0):
			hammerTeamID = cur.fetchall()[0][0]
		#New Team
		if (numrows	==	0):
			#Create Team
			cur.execute("INSERT INTO Team VALUES\
						(NULL, 0,0,0,0,0,0,0,0,0,0,0,0,0,0)")
			#Get Newly Created Team ID
			cur.execute("SELECT ID FROM Team ORDER BY id DESC LIMIT 0, 1")
			hammerTeamID = cur.fetchall()[0][0]
			
			#Insert Players into tables (Many to Many Relationship so tables need to be separated)
			cur.execute("INSERT INTO PlayerTeam VALUES \
					(" + str(hammerSkipID) + ", \
					" + str(4) + ", " + str(hammerTeamID) + "\
					)")
			cur.execute("INSERT INTO PlayerTeam VALUES \
					(" + str(hammerThirdID) + ", \
					" + str(3) + ", " + str(hammerTeamID) + "\
					)")
			cur.execute("INSERT INTO PlayerTeam VALUES \
					(" + str(hammerSecondID) + ", \
					" + str(2) + ", " + str(hammerTeamID) + "\
					)")
			cur.execute("INSERT INTO PlayerTeam VALUES \
					(" + str(hammerLeadID) + ", \
					" + str(1) + ", " + str(hammerTeamID) + "\
					)")
		
		#Get ID for Other Team
		numrows = cur.execute("SELECT TeamID FROM PlayerTeam WHERE (PlayerID = " + str(otherSkipID) + " OR PlayerID = " + str(otherThirdID) + " \
					OR PlayerID = " + str(otherSecondID) + " \
					OR PlayerID = " + str(otherLeadID) \
					+ ") GROUP BY TeamID HAVING count(*) = 4")

		#Team already exists.  
		if (numrows > 0):
			otherTeamID = cur.fetchall()[0][0]
		#New Team
		if (numrows	==	0):
			#Create Team
			cur.execute("INSERT INTO Team VALUES\
						(NULL, 0,0,0,0,0,0,0,0,0,0,0,0,0,0)")
			#Get Newly Created Team ID
			cur.execute("SELECT ID FROM Team ORDER BY id DESC LIMIT 0, 1")
			otherTeamID = cur.fetchall()[0][0]
			
			#Insert Players into tables (Many to Many Relationship so tables need to be separated)
			cur.execute("INSERT INTO PlayerTeam VALUES \
					(" + str(otherSkipID) + ", \
					" + str(4) + ", " + str(otherTeamID) + "\
					)")
			cur.execute("INSERT INTO PlayerTeam VALUES \
					(" + str(otherThirdID) + ", \
					" + str(3) + ", " + str(otherTeamID) + "\
					)")
			cur.execute("INSERT INTO PlayerTeam VALUES \
					(" + str(otherSecondID) + ", \
					" + str(2) + ", " + str(otherTeamID) + "\
					)")
			cur.execute("INSERT INTO PlayerTeam VALUES \
					(" + str(otherLeadID) + ", \
					" + str(1) + ", " + str(otherTeamID) + "\
					)")
			
			
		#Added the teams to the DB.  Just need to add the game now
		numrows = cur.execute("SELECT * FROM Game WHERE\
					GameDate='" + str(game_date) + "'\
					AND HammerTeamID='" + str(hammerTeamID) + "'\
					AND OtherTeamID='" + str(otherTeamID) + "'\
					AND Event='" + str(game_event) + "'"\
					);
		if (numrows == 0):
			cur.execute("INSERT INTO Game VALUES(\
					NULL,\
					'" + str(game_date) + "',\
					" + str(hammerTeamID) + ",\
					" + str(otherTeamID) + ",\
					'" + str(game_event) + "')"\
					);
			game_id = cur.lastrowid
		#Now add linescore
			for s in range(0, len(hammer_linescore)):
				if (hammer_linescore[s] != 'X' and other_linescore[s] != 'X'):
					cur.execute("INSERT INTO EndScore VALUES(\
					" + str(s) + ",\
					" + str(game_id) + ",\
					" + str(hammer_linescore[s]) + ",\
					" + str(other_linescore[s]) + ")")
				else:
					break
		hammer_linescore=[]
		other_linescore=[]
db.commit()

print("Compiling Stats")
compileStats.compileAllStats()		#After inserting all data into DB, compile all relevant stats