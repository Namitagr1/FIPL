import requests, re, sys, json, MySQLdb
from bs4 import BeautifulSoup


class Player:
    def __init__(self, first_last):
        self.catch_runouts = 0
        self.overs = None
        self.runsC = None
        self.wickets = None
        self.balls = None
        self.runs = None
        self.first, self.last = first_last.split(' ', 1)
        if first_last == 'Shreyas Iyer':
            self.last = 'SS Iyer'
        if first_last == 'Venkatesh Iyer':
            self.last = 'VR Iyer'

    def add_bat(self, runs, balls):
        self.runs = runs
        self.balls = balls

    def add_bowl(self, wickets, runsC, overs):
        self.wickets = wickets
        self.runsC = runsC
        self.overs = overs

    def add_field(self):
        self.catch_runouts += 1

    def __str__(self):
        return f'\t\t\t{self.runs}\t\t\t{self.balls}\t\t\t{self.wickets}\t\t\t{self.runsC}\t\t\t{self.overs}\t\t\t{self.catch_runouts}'


stats_dict = {}


def match_stat(link, innings):
    if innings not in (0, 1, 2):
        innings = 0

    page = requests.get(link)
    soup = BeautifulSoup(page.content, 'html.parser')
    s = soup.find_all('td', class_ = 'ds-min-w-max')
    s_og = [i.text for i in s]

    for i in range(len(s)):
        if s[i].text == 'Extras':
            inning1 = s[:i]
            s = s[i + 1:]
            break
    if s[0].text[0] == '(':
        s = s[2:]
    elif s[0].text.isnumeric():
        s = s[1:]
    for i in range(len(s)):
        if re.search('^[0-9]*/[0-9]$', s[i].text) or s[i].text.isnumeric():
            for j in range(i + 1, len(s)):
                if s[j].text[0:4] != 'Fall' and s[j].text[0:3] != 'Did' and s[j].text != '':
                    s = s[j:]
                    break
            break
    for i in range(len(s)):
        if s[i].text[0:2] == 'c ' or s[i].text[0:2] == 'b ' or s[i].text[0:2] == 'st' or s[i].text[0:4] == 'time' or s[i].text[0:6] == 'lbw b ' or s[i].text[0:7] == 'not out' or s[i].text[0:8] == 'run out ' or s[i].text[0:8] == 'retired ' or s[i].text[0:8] == 'obstruct' or s[i].text[0:11] == 'hit wicket ':
            inning1Bowl = s[:i - 1]
            s = s[i - 1:]
            break

    for i in range(len(s)):
        if s[i].text == 'Extras':
            inning2 = s[:i]
            s = s[i + 1:]
            break
    if s[0].text[0] == '(':
        s = s[2:]
    elif s[0].text.isnumeric():
        s = s[1:]
    for i in range(len(s)):
        if re.search('^[0-9]*/[0-9]$', s[i].text) or s[i].text.isnumeric():
            for j in range(i + 1, len(s)):
                if s[j].text[0:4] != 'Fall' and s[j].text[0:3] != 'Did' and s[j].text != '':
                    s = s[j:]
                    break
            break
    for i in range(len(s)):
        if s[i].text == 'Toss':
            inning2Bowl = s[:i - 1]

    inning1R = [i.text for i in inning1]
    inning1BowlR = [i.text for i in inning1Bowl]
    inning2R = [i.text for i in inning2]
    inning2BowlR = [i.text for i in inning2Bowl]
    dnb = []
    in_points = []

    for i in range(len(s_og)):
        if s_og[i][0:12] == 'Did not bat:':
            in_points.append(i)

    if len(in_points) == 0:
        a = 10
    elif len(in_points) == 2:
        a = in_points[0] + 1
    else:
        a = 163

    for i in s_og[:a]:
        if innings in (0, 1) and i[0:12] == 'Did not bat:':
            dnb_string = i[13:]
            x = dnb_string.replace(u'\xa0', ' ').split(', ')
            for j in x:
                dnb.append(j.strip())
    for i in s_og[a:]:
        if innings in (0, 2) and i[0:12] == 'Did not bat:':
            dnb_string = i[13:]
            x = dnb_string.replace(u'\xa0', ' ').split(', ')
            for j in x:
                dnb.append(j.strip())

    while '' in inning1R:
        inning1R.remove('')
    while '' in inning2R:
        inning2R.remove('')
    while '' in inning1BowlR:
        inning1BowlR.remove('')
    while '' in inning2BowlR:
        inning2BowlR.remove('')

    for i in inning1R:
        if innings in (0, 1) and '\xa0' in i:
            if len(i.split('\xa0')[0].split(' ')) < 2:
                stats_dict[' '.join([i.split('\xa0')[0], i.split('\xa0')[1]])] = Player(' '.join([i.split('\xa0')[0], i.split('\xa0')[1]]))
            else:
                stats_dict[i.split('\xa0')[0]] = Player(i.split('\xa0')[0])
    for i in inning2R:
        if innings in (0, 2) and '\xa0' in i:
            if len(i.split('\xa0')[0].split(' ')) < 2:
                stats_dict[' '.join([i.split('\xa0')[0], i.split('\xa0')[1]])] = Player(' '.join([i.split('\xa0')[0], i.split('\xa0')[1]]))
            else:
                stats_dict[i.split('\xa0')[0]] = Player(i.split('\xa0')[0])
    for i in dnb:
        stats_dict[i] = Player(i)
        stats_dict[i].add_bat(0, 0)

    for i in range(len(inning1R)):
        if innings in (0, 1) and '\xa0' in inning1R[i]:
            if len(inning1R[i].split('\xa0')[0].split(' ')) < 2:
                stats_dict[' '.join([inning1R[i].split('\xa0')[0], inning1R[i].split('\xa0')[1]])].add_bat(int(inning1R[i + 2]), int(inning1R[i + 3]))
            else:
                stats_dict[inning1R[i].split('\xa0')[0]].add_bat(int(inning1R[i + 2]), int(inning1R[i + 3]))
    for i in range(len(inning2R)):
        if innings in (0, 2) and '\xa0' in inning2R[i]:
            if len(inning2R[i].split('\xa0')[0].split(' ')) < 2:
                stats_dict[' '.join([inning2R[i].split('\xa0')[0], inning2R[i].split('\xa0')[1]])].add_bat(int(inning2R[i + 2]), int(inning2R[i + 3]))
            else:
                stats_dict[inning2R[i].split('\xa0')[0]].add_bat(int(inning2R[i + 2]), int(inning2R[i + 3]))

    for i in range(len(inning1BowlR) - 1, -1, -1):
        if re.search('^[0-9]*\.[0-9] to$', inning1BowlR[i][0:7].strip()):
            inning1BowlR.pop(i)
    for i in range(len(inning2BowlR) - 1, -1, -1):
        if re.search('^[0-9]*\.[0-9] to$', inning2BowlR[i][0:7].strip()):
            inning2BowlR.pop(i)

    for i in range(0, len(inning1BowlR), 11):
        if innings in (0, 2):
            stats_dict[inning1BowlR[i]].add_bowl(inning1BowlR[i + 4], inning1BowlR[i + 3], inning1BowlR[i + 1])
    for i in range(0, len(inning2BowlR), 11):
        if innings in (0, 1):
            stats_dict[inning2BowlR[i]].add_bowl(inning2BowlR[i + 4], inning2BowlR[i + 3], inning2BowlR[i + 1])

    for i in stats_dict:
        if stats_dict[i].overs is None:
            stats_dict[i].add_bowl(0, 0, 0)

    inning1RF = [i.replace('†', '') for i in inning1R]
    inning2RF = [i.replace('†', '') for i in inning2R]

    for i in range(1, len(inning1RF), 8):
        if innings in (0, 2):
            if inning1RF[i].strip()[0:3] == 'st ':
                for j in stats_dict:
                    if inning1RF[i].strip()[3:inning1RF[i].strip().index(' b ')] == stats_dict[j].last or inning1RF[i].strip()[3:inning1RF[i].strip().index(' b ')] == j or inning1RF[i].strip()[3:inning1RF[i].strip().index(' b ')] == stats_dict[j].first:
                        stats_dict[j].add_field()
                        break
            if inning1RF[i].strip()[0:2] == 'c ':
                for j in stats_dict:
                    if inning1RF[i].strip()[2:inning1RF[i].strip().index(' b ')] == stats_dict[j].last or inning1RF[i].strip()[2:inning1RF[i].strip().index(' b ')] == j or inning1RF[i].strip()[2:inning1RF[i].strip().index(' b ')] == stats_dict[j].first:
                        stats_dict[j].add_field()
                        break
            if inning1RF[i].strip()[0:6] == 'c & b ':
                for j in stats_dict:
                    if inning1RF[i][6:].strip() == stats_dict[j].last or inning1RF[i][6:].strip() == j or inning1RF[i][6:].strip() == stats_dict[j].first:
                        stats_dict[j].add_field()
                        break
            if inning1RF[i].strip()[0:8] == 'run out ':
                for j in stats_dict:
                    if '/' in inning1RF[i].strip():
                        if inning1RF[i].strip()[9:inning1RF[i].strip().index('/')] == stats_dict[j].last or inning1RF[i].strip()[9:inning1RF[i].strip().index('/')] == j or inning1RF[i].strip()[9:inning1RF[i].strip().index('/')] == stats_dict[j].first:
                            stats_dict[j].add_field()
                            break
                    else:
                        if inning1RF[i].strip()[9:inning1RF[i].strip().index(')')] == stats_dict[j].last or inning1RF[i].strip()[9:inning1RF[i].strip().index(')')] == j or inning1RF[i].strip()[9:inning1RF[i].strip().index(')')] == stats_dict[j].first:
                            stats_dict[j].add_field()
                            break
    for i in range(1, len(inning2RF), 8):
        if innings in (0, 1):
            if inning2RF[i].strip()[0:3] == 'st ':
                for j in stats_dict:
                    if inning2RF[i].strip()[3:inning2RF[i].strip().index(' b ')] == stats_dict[j].last or inning2RF[i].strip()[3:inning2RF[i].strip().index(' b ')] == j or inning2RF[i].strip()[3:inning2RF[i].strip().index(' b ')] == stats_dict[j].first:
                        stats_dict[j].add_field()
                        break
            if inning2RF[i].strip()[0:2] == 'c ':
                for j in stats_dict:
                    if inning2RF[i].strip()[2:inning2RF[i].strip().index(' b ')] == stats_dict[j].last or inning2RF[i].strip()[2:inning2RF[i].strip().index(' b ')] == j or inning2RF[i].strip()[2:inning2RF[i].strip().index(' b ')] == stats_dict[j].first:
                        stats_dict[j].add_field()
                        break
            if inning2RF[i].strip()[0:6] == 'c & b ':
                for j in stats_dict:
                    if inning2RF[i][6:].strip() == stats_dict[j].last or inning2RF[i][6:].strip() == j or inning2RF[i][6:].strip() == stats_dict[j].first:
                        stats_dict[j].add_field()
                        break
            if inning2RF[i].strip()[0:8] == 'run out ':
                for j in stats_dict:
                    if '/' in inning2RF[i].strip():
                        if inning2RF[i].strip()[9:inning2RF[i].strip().index('/')] == stats_dict[j].last or inning2RF[i].strip()[9:inning2RF[i].strip().index('/')] == j or inning2RF[i].strip()[9:inning2RF[i].strip().index('/')] == stats_dict[j].first:
                            stats_dict[j].add_field()
                            break
                    else:
                        if inning2RF[i].strip()[9:inning2RF[i].strip().index(')')] == stats_dict[j].last or inning2RF[i].strip()[9:inning2RF[i].strip().index(')')] == j or inning2RF[i].strip()[9:inning2RF[i].strip().index(')')] == stats_dict[j].first:
                            stats_dict[j].add_field()
                            break


num = int(sys.argv[1])
links_arr = sys.argv[2].replace('\\', '').strip(' {}').split(', ')
links = {}
for i in links_arr:
    links[i[:i.index(' :')]] = int(i[i.index(' :') + 2:].strip())
week = sys.argv[3]

for i in links:
    match_stat(i, links[i])

if 'Wanindu Hasaranga de Silva' in stats_dict:
    stats_dict['Wanindu Hasaranga'] = stats_dict['Wanindu Hasaranga de Silva']
    stats_dict.pop('Wanindu Hasaranga de Silva')

file = open('stats.txt', 'a')

for i in stats_dict:
    file.write(i.ljust(30) + stats_dict[i].__str__() + '\n')

db = MySQLdb.connect('localhost', 'root', '', 'users')
insert = db.cursor()
query = f"""INSERT INTO week_{week} (Player, Runs, Balls, Wickets, RunsC, Overs, Catches) VALUES (%s, %s, %s, %s, %s, %s, %s) """
for i in stats_dict:
    record = (i, stats_dict[i].runs, stats_dict[i].balls, stats_dict[i].wickets, stats_dict[i].runsC, stats_dict[i].overs, stats_dict[i].catch_runouts)
    insert.execute(query, record)
db.commit()
db.close()

# After this input stats to match pages
# If stats.txt has all 110 stats start outputting results

# TODO Make processor of stats.txt to fit with player XI, Call on matches page for linking webscraper to website
