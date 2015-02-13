import urllib2



#Is given the URL and retrieves the HTML from that page
def getHTML(url):
	retrievedUrl = False
	while (not retrievedUrl):
		try:
			source = urllib2.urlopen(str(url), timeout = 10)
			s = source.readlines()
			source.close()
			retrievedUrl = True
			return s
		except:
			print("Timeout Error")
		
		
	
