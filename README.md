# twitterApi
Symfony2 console tool for parsing twitter accounts tweets by its account name and printing frequency of keywords used in his last tweets.

##Usage:
###Command:
```bash
twitter:parse:tweets [options] [--] <username> [<number>]`
```
###Arguments:
```bash
username              Twitter account username
number                Number of tweets to parse [default: 100]
```

###Options:
```bash
--h                   parse only hashtags
```