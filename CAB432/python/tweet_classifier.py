#import regex
import re
import csv
import pprint
import nltk.classify
import time
import math
import pickle
import os.path

startTime = time.clock()

#start replaceTwoOrMore
def replaceTwoOrMore(s):
    #look for 2 or more repetitions of character
    pattern = re.compile(r"(.)\1{1,}", re.DOTALL) 
    return pattern.sub(r"\1\1", s)
#end

#start process_tweet
def processTweet(tweet):
    # process the tweets
    
    #Convert to lower case
    tweet = tweet.lower()
    #Convert www.* or https?://* to URL
    tweet = re.sub('((www\.[^\s]+)|(https?://[^\s]+))','URL',tweet)
    #Convert @username to AT_USER
    tweet = re.sub('@[^\s]+','AT_USER',tweet)    
    #Remove additional white spaces
    tweet = re.sub('[\s]+', ' ', tweet)
    #Replace #word with word
    tweet = re.sub(r'#([^\s]+)', r'\1', tweet)
    #trim
    tweet = tweet.strip('\'"')
    return tweet
#end 

#start getStopWordList
def getStopWordList(stopWordListFileName):
    #read the stopwords
    stopWords = []
    stopWords.append('AT_USER')
    stopWords.append('URL')

    fp = open(stopWordListFileName, 'r')
    line = fp.readline()
    while line:
        word = line.strip()
        stopWords.append(word)
        line = fp.readline()
    fp.close()
    return stopWords
#end

#start getfeatureVector
def getFeatureVector(tweet, stopWords):
    featureVector = []  
    words = tweet.split()
    for w in words:
        #replace two or more with two occurrences 
        w = replaceTwoOrMore(w) 
        #strip punctuation
        w = w.strip('\'"?,.')
        #check if it consists of only words
        val = re.search(r"^[a-zA-Z][a-zA-Z0-9]*[a-zA-Z]+[a-zA-Z0-9]*$", w)
        #ignore if it is a stopWord
        if(w in stopWords or val is None):
            continue
        else:
            featureVector.append(w.lower())
    return featureVector    
#end

#start extract_features
def extract_features(tweet):
    tweet_words = set(tweet)
    features = {}
    for word in featureList:
        features['contains(%s)' % word] = (word in tweet_words)
    return features
#end


#Read the tweets one by one and process it
#inpTweets = csv.reader(open('sampleTweets.csv', 'rt'), delimiter=',', quotechar='|')
inpTweets = csv.reader(open('full_training_dataset.csv', 'rt', encoding="latin-1"), delimiter=',', quotechar='"')
stopWords = getStopWordList('stopwords.txt')
count = 0;
featureList = []
tweets = []
for row in inpTweets:
    sentiment = row[0]
    tweet = row[1]
    processedTweet = processTweet(tweet)
    featureVector = getFeatureVector(processedTweet, stopWords)
    featureList.extend(featureVector)
    tweets.append((featureVector, sentiment));
#end loop

# Remove featureList duplicates
featureList = list(set(featureList))

# Generate the training set
training_set = nltk.classify.util.apply_features(extract_features, tweets)

NBClassifier = None
Text = None
# If training data is already created, load it
if os.path.isfile('naivebayes_trained_classifier.pickle'):
	# Open and load
	f = open('naivebayes_trained_classifier.pickle', 'rb')
	NBClassifier = pickle.load(f)
	f.close()
	
	Text = 'load'
	
# Otherwise we're going to have to train it (Takes around 15 minutes)
else:
	# Train the Naive Bayes classifier
	NBClassifier = nltk.NaiveBayesClassifier.train(training_set)
	
	# Save
	f = open('naivebayes_trained_classifier.pickle', 'wb')
	pickle.dump(NBClassifier, f)
	f.close()
	
	Text = 'train'

clockTime = time.clock() - startTime
remainder = clockTime % 60
if clockTime > 60:
	clockTime = math.floor(clockTime / 60)
	print ("\nTook %i minutes %.2g seconds to %s classifier\nRunning test tweets...\n" % (clockTime, remainder, Text) )
else:
	print ("\nTook %.2g seconds to %s classifier\nRunning test tweets...\n" % (clockTime, Text) )
startTime = time.clock()
# Test the classifier
testTweet = [
'Congrats @ravikiranj, i heard you wrote a new tech post on sentiment analysis',
'In even more exciting news, we\'re updating our legal documents! Enjoy!',
'Pizza for dinner is still is the best news anybody can hear',
'Excited for this week with @Celestron :)',
'My week off has officially come to an end.  I think I\'ve shown a ton of potential at doing no work ever all day.',
'I started watching Rick & Morty. It\'s excellent.'
]
for row in testTweet:
	processedTestTweet = processTweet(row)
	sentiment = NBClassifier.classify(extract_features(getFeatureVector(processedTestTweet, stopWords)))
	print ("testTweet = %s, sentiment = %s\n" % (row, sentiment))

print ("Took %.2g seconds to compute tests..." % (time.clock() - startTime) )