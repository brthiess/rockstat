import datetime

class Game:
	
	def __init__(self, date = None, linescore = None, hammerTeam = None, otherTeam = None, event = None):
		if (date is not None):
			self.date = date
		else:
			self.date = (datetime.date(2000,1,1))
		if (linescore is not None):
			self.linescore = linescore
		else:
			self.linescore = [['0'],['0']]
		if (hammerTeam is not None):
			self.hammerTeam = hammerTeam
		else:
			self.hammerTeam = 'None'
		if (otherTeam is not None):
			self.otherTeam = otherTeam
		else:
			self.otherTeam = 'None'
		if (event is not None):
			self.event = event
		else:
			self.event = 'None'
		
