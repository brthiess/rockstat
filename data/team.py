class Team:
	
	LEAD = 0
	SKIP = 1
	THIRD = 2
	SECOND = 3
	
	
	
	def __init__(self, lead = None, second = None, third = None, skip = None):
		self.playerList = []
		self.playerList.append(lead)
		self.playerList.append(second)
		self.playerList.append(third)
		self.playerList.append(skip)
		self.lead = lead
		self.second = second
		self.third = third
		self.skip = skip
		self.player_count = 0
		if (lead is not None):
			self.player_count += 1
		if (second is not None):
			self.player_count += 1
		if(third is not None):
			self.player_count += 1
		if(skip is not None):
			self.player_count += 1
			
	def playerIsNotOnTeamAlready(self, name):
		for p in self.playerList:
			if (name == p):
				return False
		return True
			
	def addPlayer(self, position, name):
		
		if(Team.playerIsNotOnTeamAlready(self, name) or Team.nameIsBlank(self, name)):
			self.player_count += 1
		self.playerList.append(name)
			
		if (position % 4 == Team.LEAD):
			self.lead = name
		elif(position % 4 == Team.SECOND):
			self.second = name
		elif(position % 4 == Team.THIRD):
			self.third = name
		elif(position % 4 == Team.SKIP):
			self.skip = name
			
	def nameIsBlank(self, name):
		if (len(name) == 0):
			return True
		else:
			return False
		
		
	
	
		
	
