#Is given the HTML for a page, and extracts the relevant information (i.e. linescores)
#from that page.
#Returns a list of games from that page

import datetime
import team
import game

LINESCORE = 'linescoreend'
HAMMERIMG = 'http://www.curlingzone.com/forums/images/hammer.gif'
HAMMER = 'linescorehammer'
ENDOFLINESCORE = '</table><br><br>'
PLAYER = 'playerid'
EVENT = 'meta property="og:title" content='
DATE = 'linescoredrawhead'
YEAR = 'Dates:'

GRAND_SLAM_PLAYER = "<td class='stats_fourthrow'>"
UPPER_SLAM_TABLE = "<table width='100%' cellpadding='0' cellspacing='0' border='1'><tr>"
SLAM_TABLE = "<td colspan=2 width='-205' class=stats_headrow><p style='margin-left: 5px'></td>"       
END_OF_SLAM_LINESCORE = '<td colspan=2 align=right>'



UPPER_TEAM = 1
LOWER_TEAM = 2

UPPER_SKIP = 1
UPPER_THIRD = 2
UPPER_SECOND = 3
UPPER_LEAD = 4
BOTTOM_SKIP = 5
BOTTOM_THIRD = 6
BOTTOM_SECOND = 7
BOTTOM_LEAD = 8

HAMMERTEAM = 0
OTHERTEAM = 1

UNKNOWN = 0




def extractInformation(html):
	games = []
	hammer = False
	player_position_iterator = UNKNOWN
	team_number = UNKNOWN
	year = None
	date = None
	event = None
	linescore = None
	
	
	upper_team_has_hammer = False
	
	hammer_team = team.Team()
	other_team = team.Team()
	for h in range(0, len(html)):
		#Get the date of the game
		#Need to get	 year first
		if (YEAR in html[h]):
			year = getYear(html[h+1])
			assert(year is not None)
		elif (DATE in html[h]):
			assert(year is not None)
			date = getDate(html[h], year)
			assert(date is not None)
		#Get the event name
		elif(EVENT in html[h]):
			event = getEvent(html[h])
		#Check if the team has hammer
		elif (HAMMER in html[h] and HAMMERIMG in html[h]):
			print("Found Hammer")
			hammer = True
		elif(HAMMER in html[h] and HAMMERIMG not in html[h]):
			print("Found Not Hammer")
			hammer = False
		#Go through HTML and check for a linescore line
		elif(LINESCORE in html[h]):		
			linescore, upper_team_has_hammer, team_number = addLinescore(html[h], linescore, team_number, hammer, upper_team_has_hammer)
			
		#If a player line was found			
		elif(PLAYER in html[h]):
			#Increment this each time a new player is found
			player_position_iterator += 1
			#Update both teams
			hammer_team, other_team = addPlayer(html, h, player_position_iterator, upper_team_has_hammer, hammer_team, other_team)
		
		#Different HTML for adding players if it is a grand slam event...
		elif(GRAND_SLAM_PLAYER in html[h]):
			hammer_team, other_team = addSlamPlayer(html, h, upper_team_has_hammer, hammer_team, other_team)
		#Found end of linescore.  Create a game out of it			
		elif(ENDOFLINESCORE in html[h] or END_OF_SLAM_LINESCORE in html[h]):
			assert (date is not None)
			assert (event is not None)
			games.append(game.Game(date, linescore, hammer_team, other_team, event))
			
			print(games[len(games)-1].otherTeam.lead)
			print(games[len(games)-1].otherTeam.second)
			print(games[len(games)-1].otherTeam.third)
			print(games[len(games)-1].otherTeam.skip)
			print(games[len(games)-1].hammerTeam.lead)
			print(games[len(games)-1].hammerTeam.second)
			print(games[len(games)-1].hammerTeam.third)
			print(games[len(games)-1].hammerTeam.skip)
			print(games[len(games)-1].date)
			print(games[len(games)-1].event)
			print(games[len(games)-1].linescore)

			
			#Reset Everything
			hammer = False
			team_number = 0
			upper_team_has_hammer = False
			player_position_iterator = 0
			hammer_team = team.Team()
			other_team = team.Team()
			linescore = None
			
	
	return games
			
			

def addPlayer(html, h, position, upper_team_has_hammer, hammer_team, other_team):			
			assert (position >= UPPER_SKIP and position <= BOTTOM_LEAD)
			#Extract the player name from HTML
			#Gets rid of useless html stuff
			player_name = html[h+3].replace("<td><b>", " ").replace("<br>", " ").replace("</b></td>", " ").split()
	
			#Series of if statements to add player to team
			if (upper_team_has_hammer):
				if (position == UPPER_SKIP):
					hammer_team.addPlayer(UPPER_SKIP, player_name)
					print("Found skip")
				elif(position == UPPER_THIRD):
					print("Found third")
					hammer_team.addPlayer(UPPER_THIRD, player_name)
				elif(position == UPPER_SECOND):
					hammer_team.addPlayer(UPPER_SECOND, player_name)
				elif(position == UPPER_LEAD):
					hammer_team.addPlayer(UPPER_LEAD, player_name)
				elif(position == BOTTOM_SKIP):
					other_team.addPlayer(BOTTOM_SKIP, player_name)
				elif(position == BOTTOM_THIRD):
					other_team.addPlayer(BOTTOM_THIRD, player_name)
				elif(position == BOTTOM_SECOND):
					other_team.addPlayer(BOTTOM_SECOND, player_name)
				elif(position == BOTTOM_LEAD):
					other_team.addPlayer(BOTTOM_LEAD, player_name)
			else:
				if (position == UPPER_SKIP):
					other_team.addPlayer(UPPER_SKIP, player_name)
				elif(position == UPPER_THIRD):
					other_team.addPlayer(UPPER_THIRD, player_name)
				elif(position == UPPER_SECOND):
					other_team.addPlayer(UPPER_SECOND, player_name)
				elif(position == UPPER_LEAD):
					other_team.addPlayer(UPPER_LEAD, player_name)
				elif(position == BOTTOM_SKIP):
					hammer_team.addPlayer(BOTTOM_SKIP, player_name)
				elif(position == BOTTOM_THIRD):
					hammer_team.addPlayer(BOTTOM_THIRD, player_name)
				elif(position == BOTTOM_SECOND):
					hammer_team.addPlayer(BOTTOM_SECOND, player_name)
				elif(position == BOTTOM_LEAD):
					hammer_team.addPlayer(BOTTOM_LEAD, player_name)
					
			return hammer_team, other_team

#Is given the line containing 'Dates: '			
def getYear(html_line):
	for year in range(2008, 2016):
		if (str(year) in html_line):
			return year
	return None


#Is given the line containing 'linescoredrawhead'
#And returns the date on that line
def getDate(html_line, year):
	month = 0
	day = 0
	#Check which month is indicated on the line
	if ('Jan' in html_line):
		#Somewhat obtuse lines of code here...
		#Grabs the day number (1 - 31), from finding the index of substring of the month
		day = html_line[html_line.index('Jan') + 4:-len(html_line) + html_line.index('Jan') + 4 + 2]
		month = 1
	elif ('Feb' in html_line):
		day = html_line[html_line.index('Feb') + 4:-len(html_line) + html_line.index('Feb') + 4 + 2]
		month = 2
	elif ('Mar' in html_line):
		day = html_line[html_line.index('Mar') + 4:-len(html_line) + html_line.index('Mar') + 4 + 2]
		month = 3
	elif ('Apr' in html_line):
		day = html_line[html_line.index('Apr') + 4:-len(html_line) + html_line.index('Apr') + 4 + 2]
		month = 4
	elif ('May' in html_line):
		day = html_line[html_line.index('May') + 4:-len(html_line) + html_line.index('May') + 4 + 2]
		month = 5
	elif ('Jun' in html_line):
		day = html_line[html_line.index('Jun') + 4:-len(html_line) + html_line.index('Jun') + 4 + 2]
		month = 6
	elif ('Jul' in html_line):
		day = html_line[html_line.index('Jul') + 4:-len(html_line) + html_line.index('Jul') + 4 + 2]
		month = 7
	elif ('Aug' in html_line):
		day = html_line[html_line.index('Aug') + 4:-len(html_line) + html_line.index('Aug') + 4 + 2]
		month = 8
	elif ('Sep' in html_line):
		day = html_line[html_line.index('Sep') + 4:-len(html_line) + html_line.index('Sep') + 4 + 2]
		month = 9
	elif ('Oct' in html_line):
		day = html_line[html_line.index('Oct') + 4:-len(html_line) + html_line.index('Oct') + 4 + 2]
		month = 10
	elif ('Nov' in html_line):
		day = html_line[html_line.index('Nov') + 4:-len(html_line) + html_line.index('Nov') + 4 + 2]
		month = 11
	elif ('Dec' in html_line):
		day = html_line[html_line.index('Dec') + 4:-len(html_line) + html_line.index('Dec') + 4 + 2]
		month = 12
	#No date found
	else:
		day = 1
		month = 1
		year = 2000
	
	assert(int(day) >= 1 and int(day) <= 31)
	assert(int(month) >= 1 and int(month) <= 12)
	assert(int(year) >= 2000 and int(year) <= 2015)
	
	date = datetime.date(year, month, int(day))
	return date

def getEvent(html_line):
	event = html_line.replace('<meta property="og:title" content="', "").replace('"/>', "")
	return event
	
def getEmptyLinescore():
	linescore = []
	linescore.append([])
	linescore.append([])
	return linescore
	
#Found a linescore line.  Split up the line so that the numbers are separated
def addLinescore(html, linescore, team_number, hammer, upper_team_has_hammer):
	linescores = html.split("&nbsp;")	
	
	#Increment the team number.  Used to keep track of 
	#whether this linescore is the top or bottom one
	team_number += 1
	
	#If this is the top linescore, and they have hammer, then note it (used later)
	if (team_number == 1 and hammer == True):
		upper_team_has_hammer = True
	#Create an empty linescore to be filled if it hasn't been created yet
	if (linescore is None):
		linescore = getEmptyLinescore()
	print("Length of Linescore: " + str(len(linescore)))
	end_number = 0
	#Go through the linescore and check for scores
	for l in linescores:
		#Check if it is an actual score and not some other random html
		if(len(l) == 1 and (l == 'X' or (int(l) >= 0 and int(l) <= 8))):
			if (hammer == True):
				print("Hammer End Number: " + str(end_number) + "  Score: " + l)
				linescore[HAMMERTEAM].append(l) 
				print(linescore[HAMMERTEAM])
			elif(hammer == False):
				print("Other End Number: " + str(end_number) + "  Score: " + l)
				linescore[OTHERTEAM].append(l)
			end_number += 1
	return linescore, upper_team_has_hammer, team_number
				
			
def addSlamPlayer(html, h, upper_team_has_hammer, hammer_team, other_team):	
		#Check if this is the upper team or the lower team
		#in the table
		#Then find the position of the player
		if (inUpperSlamTable(html, h)):
			if ('4:' in html[h-1]):
				position = UPPER_SKIP
			elif('3:' in html[h-1]):
				position = UPPER_THIRD
			elif('2:' in html[h-1]):
				position = UPPER_SECOND
			elif('1:' in html[h-1]):
				position = UPPER_LEAD
		else:
			if ('4:' in html[h-1]):
				position = BOTTOM_SKIP
			elif('3:' in html[h-1]):
				position = BOTTOM_THIRD
			elif('2:' in html[h-1]):
				position = BOTTOM_SECOND
			elif('1:' in html[h-1]):
				position = BOTTOM_LEAD
		assert (position >= UPPER_SKIP and position <= BOTTOM_LEAD)
		#Extract the player name from HTML
		#Gets rid of useless html stuff
		player_name = html[h].replace("<td class='stats_fourthrow'>&nbsp;", " ").replace("<br></td>", " ").split()
		print("Player name" + player_name[0] + str(position))
		#Series of if statements to add player to team
		if (upper_team_has_hammer):
			if (position == UPPER_SKIP):
				hammer_team.addPlayer(UPPER_SKIP, player_name)
				print("Found skip")
			elif(position == UPPER_THIRD):
				print("Found third")
				hammer_team.addPlayer(UPPER_THIRD, player_name)
			elif(position == UPPER_SECOND):
				hammer_team.addPlayer(UPPER_SECOND, player_name)
			elif(position == UPPER_LEAD):
				hammer_team.addPlayer(UPPER_LEAD, player_name)
			elif(position == BOTTOM_SKIP):
				other_team.addPlayer(BOTTOM_SKIP, player_name)
			elif(position == BOTTOM_THIRD):
				other_team.addPlayer(BOTTOM_THIRD, player_name)
			elif(position == BOTTOM_SECOND):
				other_team.addPlayer(BOTTOM_SECOND, player_name)
			elif(position == BOTTOM_LEAD):
				other_team.addPlayer(BOTTOM_LEAD, player_name)
		else:
			if (position == UPPER_SKIP):
				other_team.addPlayer(UPPER_SKIP, player_name)
			elif(position == UPPER_THIRD):
				other_team.addPlayer(UPPER_THIRD, player_name)
			elif(position == UPPER_SECOND):
				other_team.addPlayer(UPPER_SECOND, player_name)
			elif(position == UPPER_LEAD):
				other_team.addPlayer(UPPER_LEAD, player_name)
			elif(position == BOTTOM_SKIP):
				hammer_team.addPlayer(BOTTOM_SKIP, player_name)
			elif(position == BOTTOM_THIRD):
				hammer_team.addPlayer(BOTTOM_THIRD, player_name)
			elif(position == BOTTOM_SECOND):
				hammer_team.addPlayer(BOTTOM_SECOND, player_name)
			elif(position == BOTTOM_LEAD):
				hammer_team.addPlayer(BOTTOM_LEAD, player_name)
				
		return hammer_team, other_team
		
def inUpperSlamTable(html, h):
	number_of_tables_encountered = 0
	for i in range(h, 0, -1):
		if (SLAM_TABLE in html[i]):
			number_of_tables_encountered += 1
		if (UPPER_SLAM_TABLE in html[i]):
			if (number_of_tables_encountered == 2):
				return False
			elif(number_of_tables_encountered == 1):
				return True
	#Should not reach here	
	print(number_of_tables_encountered)
	assert(False)
		
if __name__ == '__main__':
	f = open('events1.html')
	html = f.readlines()
	f.close()	
	games = extractInformation(html)	

