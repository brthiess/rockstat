import MySQLdb

db = MySQLdb.connect(host="localhost", # your host, usually localhost
                     user="root", # your username
                      passwd="jikipol", # your password
                      db="rockstat") # name of the data base
					  
cur = db.cursor()

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
														
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						('" + hammer_skip_first + "', '" + hammer_skip_last + "')")
						
						
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + hammer_third_last + "%' AND FirstName LIKE '%" + hammer_third_first + "%'")
														
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						('" + hammer_third_first + "', '" + hammer_third_last + "')")
						
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + hammer_second_last + "%' AND FirstName LIKE '%" + hammer_second_first + "%'")
														
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						('" + hammer_second_first + "', '" + hammer_second_last + "')")
						
						
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + hammer_lead_last + "%' AND FirstName LIKE '%" + hammer_lead_first + "%'")
														
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						('" + hammer_lead_first + "', '" + hammer_lead_last + "')")
						
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + other_skip_last + "%' AND FirstName LIKE '%" + other_skip_first + "%'")
														
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						('" + other_skip_first + "', '" + other_skip_last + "')")
						
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + other_third_last + "%' AND FirstName LIKE '%" + other_third_first + "%'")
														
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						('" + other_third_first + "', '" + other_third_last + "')")
						
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + other_second_last + "%' AND FirstName LIKE '%" + other_second_first + "%'")
														
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						('" + other_second_first + "', '" + other_second_last + "')")
						
		numrows = cur.execute("SELECT * FROM Player WHERE LastName LIKE '%" + other_lead_last + "%' AND FirstName LIKE '%" + other_lead_first + "%'")
														
		if (numrows == 0):
			cur.execute("INSERT INTO Player VALUES \
						('" + other_lead_first + "', '" + other_lead_last + "')")
						
		
						
								
		#All data for a game has been obtained.
		#Put data from the game into sql
		#Check to make sure team is not a duplicate
		numrows = cur.execute("SELECT ID FROM Team WHERE SkipLast = '" + hammer_skip_last + "' AND ThirdLast = '" + hammer_third_last + "' \
					AND SecondLast = '" + hammer_second_last + "' \
					AND LeadLast = '" + hammer_lead_last \
					+ "'")
		hammer_team_id = -1
		#Team already exists.  
		if (numrows > 0):
			hammer_team_id = cur.fetchall()[0][0]
		#New Team
		if (numrows	==	0):
			cur.execute("INSERT INTO Team VALUES \
					(NULL, \
					'" + hammer_skip_last + "', '" + hammer_third_last + "', \
					'" + hammer_second_last + "', '" + hammer_lead_last + "', \
					'" + hammer_skip_first + "', '" + hammer_third_first + "', \
					'" + hammer_second_first + "', '" + hammer_lead_first + "' \
					)")
			#Get the latest ID
			cur.execute("SELECT ID FROM Team ORDER BY id DESC LIMIT 0, 1")
			hammer_team_id = cur.fetchall()[0][0]
			
		#Get ID for other team
		numrows = cur.execute("SELECT ID FROM Team WHERE SkipLast = '" + other_skip_last\
					+ "' AND ThirdLast = '" + other_third_last \
					+ "' AND SecondLast = '" + other_second_last\
					+ "' AND LeadLast = '" + other_lead_last \
					+ "'")
		other_team_id = -1
		#Check to see if other team already in db
		if (numrows > 0):
			other_team_id = cur.fetchall()[0][0]
		#New team
		elif (numrows	==	0):
			cur.execute("INSERT INTO Team VALUES \
					(NULL, \
					'" + other_skip_last + "', '" + other_third_last + "', \
					'" + other_second_last + "', '" + other_lead_last + "', \
					'" + other_skip_first + "', '" + other_third_first + "', \
					'" + other_second_first + "', '" + other_lead_first + "' \
					)")
			cur.execute("SELECT ID FROM Team ORDER BY id DESC LIMIT 0, 1")
			other_team_id = cur.fetchall()[0][0]
		#Added the team to the DB.  Just need to add the game now
		numrows = cur.execute("SELECT * FROM Game WHERE\
					GameDate='" + str(game_date) + "'\
					AND HammerTeamID='" + str(hammer_team_id) + "'\
					AND OtherTeamID='" + str(other_team_id) + "'\
					AND Event='" + str(game_event) + "'"\
					);
		if (numrows == 0):
			cur.execute("INSERT INTO Game VALUES(\
					NULL,\
					'" + str(game_date) + "',\
					" + str(hammer_team_id) + ",\
					" + str(other_team_id) + ",\
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