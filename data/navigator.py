import datetime
import extractor


#Is given the html for a page, and decides which URL to visit next
EVENT = "Arial,Helvetica,Geneva"
EVENTLINK = "A HREF="
SCORES = ">Scores</a></td>"
HTTPWORLDCURL = "http://www.worldcurl.com/"
EVENTSURL = "http://www.worldcurl.com/schedule.php?eventtypeid=21&eventyear=2013"
LINESCORE = "linescoredrawlink"
DRAWLINK = "showdrawid"
VIEWPREVIOUSSEASON = 'View Previous Season'
YEAR = 'Dates:'
DATE = 'linescoredrawhead'


MEN_2012 = 'http://www.worldcurl.com/schedule.php?eventtypeid=21&eventyear=2012'
MEN_2013 = 'http://www.worldcurl.com/schedule.php?eventtypeid=21&eventyear=2013'
MEN_2014 = 'http://www.worldcurl.com/schedule.php?eventtypeid=21&eventyear=2014'
MEN_2015 = 'http://www.worldcurl.com/schedule.php?eventtypeid=21'
WOMEN_2012 = 'http://www.worldcurl.com/schedule.php?eventtypeid=51&eventyear=2012'
WOMEN_2013 = 'http://www.worldcurl.com/schedule.php?eventtypeid=51&eventyear=2013'
WOMEN_2014 = 'http://www.worldcurl.com/schedule.php?eventtypeid=51&eventyear=2014'
WOMEN_2015 = 'http://www.worldcurl.com/schedule.php?eventtypeid=51'
visitedUrls = []

#Figure out the next page to visit
#starting_date: 	The latest date of the games recorded in the games.dat file.  Don't bother
#					visiting events that have already been recorded in the file  
def getNextPage(html, web_url, starting_date):
	print("The latest date in games.dat: " + str(starting_date))
	#Go through the HTML line by line, check for relevant links
	for line in html:
		#Check for events first.  Check to make sure event is not older than the latest date (i.e. already in games.dat)
		if (EVENT in line and EVENTLINK in line and eventIsNotOlderThanLatestDate(web_url, html, line, starting_date) and eventHasBeenPlayed(web_url, html, line)):
			#Grab the event URL
			print("Navigator decided to visit this event")
			url = line.split("<A HREF=")
			url = url[1].split(">")
			if (hasNotBeenVisitedYet(url[0])):
				visitedUrls.append(url[0])
				return HTTPWORLDCURL + url[0]
		#Check for the scores link next
		elif (SCORES in line):
			url = line.split("href=")
			url = url[1].split(">")
			if (hasNotBeenVisitedYet(url[0])):
				visitedUrls.append(url[0])
				return HTTPWORLDCURL + url[0]
		#Finally, check for the link to specific draws
		elif (LINESCORE in line):
			url = line.replace("href='", " ").replace("'>", " ").split()
			for u in url:
				if (DRAWLINK in u and hasNotBeenVisitedYet(u)):
					visitedUrls.append(u)
					return HTTPWORLDCURL + u
					
	#If nothing found, go back to beginning of page or new schedule page
	return getCorrectSchedulePage(html, web_url)


#Checks to see if a url has been visited by the crawler already
def hasNotBeenVisitedYet(url):
	if url in visitedUrls:
		return False
	else:
		return True
		
#Checks to see if every event for every year for both genders has been visited
def getCorrectSchedulePage(html, web_url):
		
		#If we have hit the end of a schedules page
		#Append that url to the list and find a new schedule page
		#Else do not append the schedule url to the visited urls
		for h in html:
			if (VIEWPREVIOUSSEASON in h):	
				visitedUrls.append(web_url)
		if (hasNotBeenVisitedYet(MEN_2012)):
			return MEN_2012
		elif(hasNotBeenVisitedYet(MEN_2013)):
			return MEN_2013
		elif(hasNotBeenVisitedYet(MEN_2014)):
			return MEN_2014
		elif(hasNotBeenVisitedYet(MEN_2015)):
			return MEN_2015
		elif(hasNotBeenVisitedYet(WOMEN_2012)):
			return WOMEN_2012
		elif(hasNotBeenVisitedYet(WOMEN_2013)):
			return WOMEN_2013
		elif(hasNotBeenVisitedYet(WOMEN_2014)):
			return WOMEN_2014
		elif(hasNotBeenVisitedYet(WOMEN_2015)):
			return WOMEN_2015
		else:
			return None

#Checks if the current event being looked at has a date that 
#is older than the latest date in the games.dat file
def eventIsNotOlderThanLatestDate(web_url, html, line, starting_date):
	for h in range(0, len(html)):
		if (line in html[h]):
			event_date = getDate(web_url, html[h+4])
			print("Navigator found this date for an event: " + str(event_date))
			#If the event happened after the latest event in the data file 
			#then we can look at this event for linescores.  
			#Else return false so we don't bother looking for it
			if (event_date >= starting_date):
				print("...And decided to visit it")
				return True
			else:
				print("And decided not to visit it")
				return False
				
def eventHasBeenPlayed(web_url, html, line):
	todays_date = datetime.datetime.now()
	for h in range(0, len(html)):
		if (line in html[h]):
			event_date = getDate(web_url, html[h+4])
			print("Navigator found this date for an event: " + str(event_date))
			#If the event happens after todays date 
			#then return false
			#else return true
			if (event_date <= todays_date):
				print("...And decided to visit it")
				return True
			else:
				print("And decided not to visit it")
				return False 
	
#Is given the line of html that contains the event date on the schedules page
#and parses it out and returns it	
def getDate(web_url, html_line):
	#Returns the year the event is played in
	year = getYear(web_url, html_line)
	month = 0
	day = getDay(html_line)
	#Check which month is indicated on the line
	if ('Jan' in html_line):
		month = 1
	if ('Feb' in html_line):
		month = 2
	if ('Mar' in html_line):
		month = 3
	if ('Apr' in html_line):
		month = 4
	if ('May' in html_line):
		month = 5
	if ('Jun' in html_line):
		month = 6
	if ('Jul' in html_line):
		month = 7
	if ('Aug' in html_line):
		month = 8
	if ('Sep' in html_line):
		month = 9
	if ('Oct' in html_line):
		month = 10
	if ('Nov' in html_line):
		month = 11
	if ('Dec' in html_line):
		month = 12
	if ('Dec' in html_line and 'Jan' in html_line):
		month = 1
		
	assert(int(day) >= 1 and int(day) <= 31)
	assert(int(month) >= 1 and int(month) <= 12)
	assert(int(year) >= 2000 and int(year) <= 2015)
	
	date = datetime.datetime(year, month, int(day))
	return date
	
	
#Is given the line of html referring to the specific event
#and the url of the schedules page
#Returns the year the event was played in
def getYear(web_url, html_line):
	#Figure out which year it is based off the URL
	if (MEN_2012 in web_url):
		year = 2011
	elif (MEN_2013 in web_url):
		year = 2012
	elif (MEN_2014 in web_url):
		year = 2013
	elif (MEN_2015 in web_url):
		year = 2014
	elif (WOMEN_2012 in web_url):
		year = 2011
	elif (WOMEN_2013 in web_url):
		year = 2012
	elif (WOMEN_2014 in web_url):
		year = 2013
	elif (WOMEN_2015 in web_url):
		year = 2014
	
	#If the event was in the later months, then the year must be incremented
	if ('Jan' in html_line):
		year += 1
	elif('Feb' in html_line):
		year += 1
	elif('Mar' in html_line):
		year += 1
	elif('Apr' in html_line):
		year += 1
	elif('May' in html_line):
		year += 1
	
	return year
	
#Is given the line of html containing the day, and parses it out and returns it
def getDay(html_line):
	#Traverse the line one character at a time starting from the end
	for h in range(len(html_line) - 1, -1, -1):		
		#Check for double digits
		if (html_line[h].isdigit() and html_line[h-1].isdigit()):
			day = str(html_line[h-1]) + str(html_line[h])
			return int(day)
		#Check for a single digit date
		elif(html_line[h].isdigit() and html_line[h-1].isspace()):
			day = int(html_line[h])
			return day
	
	#Should not get here
	assert(False)


#Checks to make sure that the game is not already in the games.dat
#Is given the full html of the page
#def gameIsNotOlderThanLatestDate(html, starting_date):
	#for h in html:
		#if (YEAR in h):
			#year = extractor.getYear(h)
		#if (DATE in h):
			#game_date = extractor.getDate(h, year)
			#if (game_date > starting_date):
				#return True
			#else:
				#return False
	##Should not get here
	#assert(False)
	

	



	

