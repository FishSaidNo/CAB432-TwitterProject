# Fiddling around with (very) basic Twitter reading from StreamAPI
#
# Uses Python 3.4.3 and Tweepy API for Python.
# Also have NLTK and Numpy installed, but to be used soon.
#
# Lots of expansion to do here, and probably won't actually be using
# Tweepy dispite the fucktonne of time it took to get it all working.
# (Can PM me about that if you'd like to install it)
#
# - Corey

# Import the necessary modules from Tweepy API (Simplified Twitter API for Python)
from tweepy.streaming import StreamListener
from tweepy import OAuthHandler
from tweepy import Stream

# Twitter API access keys. These are my personal Twitter API keys, please don't share
access_token = "306689174-piN0juIBvqgvhUW7XrNOQj850CbW0bMze7b6hcti"
access_token_secret = "iPEM7ym9d5ioxBFTBOyye3eGsbnn7Pek9yL4MIsNswYJy"
consumer_key = "2WoPFbAiFpUuS89dpWkhD76JZ"
consumer_secret = "JpMgkq0QmhthWNajVGdM0Yy62DWevNn1V7vK9m51A4B2zvwA9w"

# Basic listener that just prints received Tweets to stdout
class StdOutListener(StreamListener):

    def on_data(self, data):
        print (data)
        return True

    def on_error(self, status):
        print (status)

# Start main class
if __name__ == '__main__':

    # This handles Twitter authentification and the connection to Twitter Streaming API
    l = StdOutListener()
    auth = OAuthHandler(consumer_key, consumer_secret)
    auth.set_access_token(access_token, access_token_secret)
    stream = Stream(auth, l)

    # This line filters Twitter Streams to capture data by the keywords: 'python', 'javascript', 'ruby'
    stream.filter(track=['python', 'javascript', 'ruby'])
