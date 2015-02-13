import navigator
import retriever
import extractor
import time
import datetime
import shelve



HAMMERTEAM = 0
OTHERTEAM = 1


#Starting URL
URL = 'http://www.worldcurl.com/schedule.php?eventtypeid=21&eventyear=2012'
GAMEFILELOCATION = 'games.dat'
#Crawls the WCT website and extracts all available games
#Stores the information in a local file
def getGames():
	notAllGamesFound = True
	gameData = []
	#Get initial HTML from the starting URL address
	html = retriever.getHTML(URL)

	#What the earliest date an event can be to have its information extracted
	starting_date = getStartingDate()
	
	#Get URL for next page
	url = navigator.getNextPage(html, URL, starting_date)


	while(url is not None):
		#Delay for politeness
		time.sleep(0.5)
		#Get the HTML source from the URL
		html = retriever.getHTML(url)
		print("URL: " + url)
		#Get the game data and add it to previous game data
		gameData = extractor.extractInformation(html)
		#Add the games to the database
		addGames(gameData)
		#Get URL for next page
		url = navigator.getNextPage(html, url, starting_date)
		
		
def addGames(gameData):	

	gameFile = open(GAMEFILELOCATION, 'a')

	
	#Write all information to a file
	for g in gameData:
		#Write the date
		gameFile.write("_d\n")
		gameFile.write(str(g.date) + '\n')
		#Write the linescore
		gameFile.write("_lh\n")
		gameFile.write(str(g.linescore[HAMMERTEAM]) + '\n')
		gameFile.write("_lo\n")
		gameFile.write(str(g.linescore[OTHERTEAM]) + '\n')
		#Write the team with the hammer
		gameFile.write("_ht\n")
		#Write in lead, second, third, skip
		gameFile.write("_hl\n")
		gameFile.write(str(g.hammerTeam.lead) + '\n')
		gameFile.write("_hs\n")
		gameFile.write(str(g.hammerTeam.second) + '\n')
		gameFile.write("_ht\n")
		gameFile.write(str(g.hammerTeam.third) + '\n')
		gameFile.write("_hf\n")
		gameFile.write(str(g.hammerTeam.skip) + '\n')
		gameFile.write("_ot\n")
		#Write in lead, second, third, skip
		gameFile.write("_ol\n")
		gameFile.write(str(g.otherTeam.lead) + '\n')
		gameFile.write("_os\n")
		gameFile.write(str(g.otherTeam.second) + '\n')
		gameFile.write("_ot\n")
		gameFile.write(str(g.otherTeam.third) + '\n')
		gameFile.write("_of\n")
		gameFile.write(str(g.otherTeam.skip) + '\n')
		#Write in event
		gameFile.write("_e\n")
		gameFile.write(str(g.event) + '\n')
	gameFile.close()
		
		
def updateGames():
	getGames()
	#TODO: Remove Duplicate games
	removeDuplicates()
	

#Gets the latest date of the games in the games.dat file
def getStartingDate():
	f = open(GAMEFILELOCATION, 'r')	
	#Put files into an array
	gameFile = f.readlines()
	#Close file
	f.close()
	#strip each entry in the array of the \n
	gameFile = [x.strip('\n') for x in gameFile]	
		
		
		
	starting_date = datetime.datetime(2000,1,1)
	for g in range(0, len(gameFile)):
		if ('_d' in gameFile[g]):
			date_on_line = str(gameFile[g+1])
			try:
				event_date = datetime.datetime.strptime(date_on_line, '%Y-%m-%d')
				if (event_date >= starting_date):
					starting_date = event_date
			except ValueError:
				print "Incorrect format"
			
			
	return starting_date
	
	
def removeDuplicates():
	f = open(GAMEFILELOCATION, 'r')	
	#Put files into an array
	games_dat = f.readlines()
	#Close file
	f.close()
	#strip each entry in the array of the \n
	games_dat = [x.strip('\n') for x in games_dat]
	
	game_date = ''
	game_linescore = ''
	game_skip = ''
	game_event = ''
	
	finished = False
	started = False
	
	duplicates = []
	
	#Find duplicates
	for g in range(0, len(games_dat)):
		if (finished == True):
			for h in xrange(g+2, len(games_dat)-25, 27 ):
				if (games_dat[h+1] == game_date and \
				games_dat[h+3] == game_linescore and \
				games_dat[h+10] == game_skip and \
				games_dat[h+25] ==  game_event):					
					print("Duplicate Found on " + str(h))
					duplicates.append(h)
			finished = False
		elif ('_d' in games_dat[g]):
			finished = False
			started = True
			game_date = games_dat[g+1]
		elif ('_lh' in games_dat[g]):
			game_linescore = games_dat[g+1]
		elif ('_hs' in games_dat[g]):
			game_skip = games_dat[g+1]
		elif ('_e' in games_dat[g]):
			finished = True
			started = False
			game_event = games_dat[g+1]
	
	
	#Create a duplicate file 		
	f = open(GAMEFILELOCATION, 'w')
	
	#Write in all of the games from the previous file 
	#Without the duplicate games
	for line in xrange(0, len(games_dat), 27):
		if (line in duplicates):
			line += 27
			continue
		
		for l in range(line, line + 27):
			f.write(games_dat[l] + '\n')
	
		
	
if __name__ == 'main':
	updateGames()
		
	
