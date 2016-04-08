#!/usr/bin/python

import calendar
import time
import urllib
import urllib2
from urllib2 import urlopen as _urlopen
import struct
import re
import cStringIO

import simplejson

try :
    from pyzabbix import ZabbixAPI
    from zabbiximg import ZabbixImg
    has_zabbix_api = True
except ImportError :
    has_zabbix_api = False

warning_cre = re.compile(r'<br />.*<br />\n', re.S)

opener = urllib2.build_opener(urllib2.HTTPCookieProcessor())
urllib2.install_opener(opener)

def urlopen(*args):
    return _urlopen(*args)

def get_image_info(data):
    size = len(data)
    height = -1
    width = -1
    content_type = ''

    if ((size >= 24) and (data[:8] == '\211PNG\r\n\032\n')
          and (data[12:16] == 'IHDR')):
        content_type = 'image/png'
        w, h = struct.unpack(">LL", data[16:24])
        width = int(w)
        height = int(h)

    # Maybe this is for an older PNG version.
    elif (size >= 16) and (data[:8] == '\211PNG\r\n\032\n'):
        # Check to see if we have the right content type
        content_type = 'image/png'
        w, h = struct.unpack(">LL", data[8:16])
        width = int(w)
        height = int(h)

    return content_type, width, height

def get_param_monthly(year, month):
    day, last = calendar.monthrange(year, month)
    start = int(time.mktime((year, month, 1, 0, 0, 0, 0, 1, 0)))
    end = int(time.mktime((year, month, last, 23, 59, 60, 0, 1, 0)))
    return end-start, start, end

def get_param_range(start, end):
    start = int(start)
    end = int(end)
    return end-start, start, end

class IRG:
    def __init__(self, url, user, passwd, verbose=True) :
        self.url = url
        self.user = user
        self.passwd = passwd
        self.verbose = verbose

    def get_report_by_name(self, name) :
        '''
        This function return dictionary object corresponding to report name
        with the following keywords. All values will be used as argument
        of get_stat and get_graph_image so the format should correspond to
        how each function interprete the data

        - title(optional) - Custom title for this report (to be put in ODT)
        - graph_ids - List of graph id in the corresponding report
        - rratype_id - type id of the RRA (used for Cacti)
        - begin_prime - start of prime (working) time
        - end_prime - end of prime (working) time
        '''

    def get_stat(self, graph_id, rra_id, timespan, start_time, end_time, start_prime, end_prime):
        '''
        Generate statistics (table after each graph).
        Return dictionary object with the following members

        - meta - A dictionary object
         - title - Title of the graph
         - graph_id - ID of the graph (used as temporary file name in ODT)
        - cols - List of dictionary object represent data in each row
         - title - Title of data (Ex: Load average 1min)
         - avg - Average of this value
         - max - Peak value
         - p_avg - Primetime average
         - pre_avg - Previous average
         - pre_max - Previous maximum (peak)
         - pre_p_avg - Previous primetime average

         NOTE: If cols is not available, stat printing will be ignored
        '''
        pass

    def get_graph_image(self, graph_id, rra_id, start_time, end_time):
        '''
        Return PNG image of the corresponding graph_id, in binary form
        '''
        pass

class ZabbixIRG(IRG) :
    def __init__(self, url, user, passwd, verbose=True) :
        IRG.__init__(self, url, user, passwd, verbose)
        if not has_zabbix_api :
            raise ImportError('No ZabbixAPI found')
        self.zapi = ZabbixAPI(self.url)
        self.zapi.login(self.user, self.passwd)
        self.zbx_img = ZabbixImg(self.url, self.user, self.passwd)
        #self.graph_cache = {}

    def get_report_by_name(self, name) :
        '''
        Generate graph id(s) for the correspond screen
        '''
        screens = self.zapi.screen.get(filter={"name":name},
            output='extend',
            limit='10',
        )
        if not screens :
            raise KeyError("Screen name %s could not be found" % (name))
        screen = screens[0]
        #print screen

        retval = {}
        retval['title'] = name
        retval['rratype_id'] = None
        retval['begin_prime'] = None
        retval['end_prime'] = None

        screen_items = self.zapi.screenitem.get(screenids=screen["screenid"],
            output='extend',
            limit='5000',
        )
        retval['graph_ids'] = []
        #print screen_items
        # sort screen by Y coordinate
        def screen_item_cmp(x, y) :
            retval = cmp(int(x["x"]), int(y["x"]))
            if retval == 0 :
                retval = cmp(int(x["y"]), int(y["y"]))
            return retval

        screen_items_sorted = sorted(screen_items, screen_item_cmp)
        for screen_item in screen_items_sorted :
            #print screen_item
            if int(screen_item["resourcetype"]) != 0 :
                continue
            retval['graph_ids'].append(screen_item["resourceid"])

        #print retval
        return retval

    def get_stat(self, graph_id, rra_id, timespan, start_time, end_time, start_prime, end_prime):
        '''
        Ignore all stat for now, return only graph name here
        '''
        # graph = self.zapi.graph.get(graphids = graph_id, expandname = True, output = 'extend')
        # if not graph :
        #     raise KeyError('Graph %s not found' % (graph_id))
        # #print graph
        # retval = {
        #     'meta': {
        #         'title': graph[0]['name'],
        #         'graph_id': graph[0]['graphid']
        #     }
        # }

        # Since the graph name from Zabbix has no hostname and hostname is already presented
        # inside the graph image, no need to put the extra title text here.
        # Just return the graph_id as is
        retval = {
            'meta': {
                'graph_id': graph_id
            }
        }

        return retval

    def get_graph_image(self, graph_id, rra_id, start_time, end_time):
        retval = cStringIO.StringIO()
        self.zbx_img.fetch_img(graph_id, start_time, end_time, retval,
            width = 500, height = 50)
        return retval.getvalue()

class CactiIRG(IRG):
    def __init__(self, url, user, passwd, verbose=True):
        IRG.__init__(self, url, user, passwd, verbose)
        self._verbose = self.verbose
        self.cacti_url = self.url+'index.php'
        self.graph_url = self.url+'graph_image.php'
        self.repoti_url = self.url+'plugins/repoti/repoti.php'
        self.login(self.user, self.passwd)

    def verbose(self, msg):
        if self._verbose:
            print msg

    def login(self, user, passwd):
        data = {'action': 'login',
                'login_username': user,
                'login_password': passwd}
        fd = urlopen(self.cacti_url, urllib.urlencode(data))
        fd.read()

    def get_rras(self):
        data = {'c': 'rras',
                'a': 'get'}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        return simplejson.loads(json)

    def get_rra_by_id(self, id):
        data = {'c': 'rras',
                'a': 'get',
                'id': str(id)}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        return simplejson.loads(json)

    def get_hosts(self):
        data = {'c': 'hosts',
                'a': 'get'}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        return simplejson.loads(json)

    def get_graphs_by_host(self, host_id):
        data = {'c': 'graphs',
                'a': 'getByHostId',
                'hostId': str(host_id)}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        return simplejson.loads(json)

    def get_reports(self):
        data = {'c': 'reports',
                'a': 'get'}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        reports = []
        for report in simplejson.loads(json):
            report['graph_ids'] = report['graph_ids'].split(',')
            reports.append(report)
        return reports

    def get_report_by_name(self, name):
        for report in self.get_reports():
            if report['template_name'] == name:
                return report

    def get_stat(self, graph_id, rra_id, timespan,
                 start_time, end_time, start_prime, end_prime):
        data = {'c': 'graphs',
                'a': 'getstat',
                'graphId': str(graph_id),
                'rraTypeId': str(rra_id),
                'timespan': str(timespan),
                'graphStart': str(start_time),
                'graphEnd': str(end_time),
                'beginPrime': start_prime,
                'endPrime': end_prime}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        json = warning_cre.sub('', json)
        try:
            o = simplejson.loads(json)
        except Exception, why:
            print self.repoti_url+'?'+urllib.urlencode(data)
            print json
            o = None
        return o

    def get_graph_image(self, graph_id, rra_id, start_time, end_time):
        data = {'local_graph_id': str(graph_id),
                'rra_id': str(rra_id),
                'graph_start': str(start_time),
                'graph_end': str(end_time)}
        fd = urlopen(self.graph_url+'?'+urllib.urlencode(data))
        content = fd.read()
        return content

if __name__ == '__main__':
    irg = IRG('http://127.0.0.1/cacti/', 'admin', 'xxx')
    print irg.get_rras()
    print irg.get_rras(3)
    print irg.get_hosts()
    print irg.get_graphs_by_host(2)
    print irg.get_reports()
    print irg.get_stat(132, 3, 2678400, 1288717200, 1291395600, '08:00', '12:00')
    #print irg.get_graph_image(132, 3, 1288717200, 1291395600)
    print get_param_monthly(2010, 11)
