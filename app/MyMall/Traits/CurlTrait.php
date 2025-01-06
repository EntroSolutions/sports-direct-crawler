<?php

namespace App\MyMall\Traits;

trait CurlTrait
{
    public function request($url)
    {

        return shell_exec('timeout 10 torify ' . base_path('curl-impersonate-v0.5.4.x86_64-linux-gnu/'.$this->getRandomUserBrowser()) . ' -s ' . $url);

//        $command = "torify curl -s '".$url."' \\
//                  -H 'authority: bg.sportsdirect.com' \\
//                  -H 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7' \\
//                  -H 'accept-language: en-US,en;q=0.9,bg;q=0.8,fr;q=0.7,de;q=0.6' \\
//                  -H 'cache-control: no-cache' \\
//                  -H 'pragma: no-cache' \\
//                  -H 'sec-ch-ua: \"Chromium\";v=\"110\", \"Not A(Brand\";v=\"24\", \"Google Chrome\";v=\"110\"' \\
//                  -H 'sec-ch-ua-mobile: ?0' \\
//                  -H 'sec-ch-ua-platform: \"Linux\"' \\
//                  -H 'sec-fetch-dest: document' \\
//                  -H 'sec-fetch-mode: navigate' \\
//                  -H 'sec-fetch-site: none' \\
//                  -H 'sec-fetch-user: ?1' \\
//                  -H 'upgrade-insecure-requests: 1' \\
//                  -H 'user-agent: ".$this->getRandomUserAgent()."' \\
//                  --compressed";
//
//
//        return shell_exec($command);


//        $curl_handle = curl_init();
//        curl_setopt($curl_handle, CURLOPT_URL,$url);
//        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
//        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl_handle, CURLOPT_PROXY, "127.0.0.1:9050");
//        curl_setopt($curl_handle, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
//        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false );
//        curl_setopt($curl_handle, CURLOPT_USERAGENT, $this->getRandomUserAgent() );
//
//        curl_setopt($curl_handle, CURLOPT_ENCODING, 'gzip, deflate');
//
//        $headers = array();
//        $headers[] = 'Authority: bg.sportsdirect.com';
//        $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7';
//        $headers[] = 'Accept-Language: en-US,en;q=0.9,bg;q=0.8,fr;q=0.7,de;q=0.6';
//        $headers[] = 'Cache-Control: no-cache';
//        $headers[] = 'Cookie: _ENV["TS01a19d95=01e4dc9a76187e9a76be19252311098c670e3f2861ceb669ff84014e8d54ce33830642e7c2cb41586f343dc7ddbcd9c684730d3be2; selectedLevel2MenuTabId=0; _ALGOLIA=anonymous-0b020520-2f68-49ce-948b-1783c0dcf26d; rxVisitor=1667918723630BM1JC2GN9TH7M00JV2RC3NLVGT97O69S; CountryRedirectCheckIsDone=true; OptanonAlertBoxClosed=2022-11-08T14:45:27.668Z; _cq_duid=1.1667918727.JIbRGlgkj7jV4AAT; _cq_suid=1.1667918727.5XAI54xRSS7fma6h; scarab.visitor=%2257603F902ECA2828%22; _cs_c=1; TS01fb1850=01e4dc9a7695e6d71970f959691cabf78b994b2c30c1746200b2d0df8c3d436b8f978e21175461d56f37f8ddc208e7610444cd20f21d6bdf400ab84982d4bb28d48c8c2f37; _pin_unauth=dWlkPU16RTNaREZoTWpJdE4yUmlZUzAwTkRZeUxXSmlZVE10TUdJeU56RXlOakppWkRJMw; _dyfs=1667919046317; _dyid_server=779123647736081488; _fbp=fb.1.1668781327547.1672637787; _tt_enable_cookie=1; _ttp=6_Vqhn-LdnrFwhw2b5GihxZEBit; scarab.profile=%2270104003-548996%7C1672761736%22; _ga=GA1.3.1318236147.1667918728; dtCookie=v_4_srv_2_sn_GR1L1RPG37MV8TDTC2N947KMBMH1HV32_app-3Af1e5442b1bc1a35c_1_app-3A48fcdf3c4ff1a638_1_ol_0_perc_100000_mul_1_rcs-3Acss_0; BVBRANDID=9cb8ec9d-b722-41a1-8458-ef88ff816f24; SportsDirectBulgaria_AuthenticationCookie=d2fe2a84-3be5-446f-8e39-7d21ecdf59b5; _gcl_au=1.1.1518610981.1676016582; _dy_c_exps=; _dy_c_att_exps=; _dycnst=dg; _dyid=779123647736081488; _dycst=dk.l.c.ws.; _dy_geo=BG.EU.BG_22.BG_22_Sofia; _dy_df_geo=Bulgaria..Sofia; ajs_anonymous_id=6abe285f-cba9-409d-93c6-1148c645efa1; SportsDirectBulgaria_AnonymousUserCurrency=GBP; dy_fs_page=www.sportsdirect.com; X-SD-URep=e970fc7c-6b2d-43fd-b9b7-ce8b7b1297de; TS01f2aabf=01e4dc9a769ba7a9784a1b3bb081bf6c685858ca876ddd49fa122454e4f5097788af0816661bf7071cea3a00e45c178c8dc8854c4cd1918d1184a0ee133e952879d177cc8455d9c458175573b7781ad26019eb80d310abf3d2c1f63d61509e166d146e9014813243289403c9b804b3eefbc67793bb3f7a86b98355b7b2243cbd7b6ea2be1456785e387569faa8ebb488de5b924e8d91def35b832aada0fde08129a0f7759d; _gid=GA1.2.1973936381.1677071066; _abck=0910DE2F6D469175103B5DFC8F8301FA~0~YAAQ5xdlXy/MATGGAQAAQ8vIfQmy/vkxG883hh96th+nj7CWtL2X9kFRLdNZ8pzBDNR2krR4lo5C07wMAEuikttcze6jC4o3I+2zXEKQMG+LTmaZ+qu3WPbqI1OPZEdOAHR1LyWgcQP0hjUPxjtG+U2zsEzYFvqRirkt8R9hVc19Ws7ivmenJcsV6qZUMoSEuuXIio3xDPDl2o9UEmVyRYxK68Kainj2ol/rnzKEeDenntnUVcg91LqypikUbeZXepdkWQ6mCj8tFZfozOptmyOCAyYDYjEM2lTeohfxenbmaV+tAGk3+v8D/wLJIwQoqAYMCy9DbBF2PlTqiHMuTLfKXH5maYFYKFVXSUsH/h1jePVPpD9OC6LB5qMtfW8GBRfX1WyRRPf7dBo+k0lXKYUHHVQBinvE7c8IN3c=~-1~-1~-1; _dy_toffset=0; dtLatC=1; dtSa=-; OptanonConsent=isGpcEnabled=0&datestamp=Thu+Feb+23+2023+12%3A21%3A34+GMT%2B0200+(Eastern+European+Standard+Time)&version=202208.1.0&isIABGlobal=false&hosts=&consentId=66ced1a4-ae8f-4fec-9d4f-9ff7de3ef27b&interactionCount=1&landingPath=NotLandingPage&groups=C0001%3A1%2CC0002%3A1%2CC0003%3A1%2CC0004%3A1&geolocation=BG%3B22&AwaitingReconsent=false; _uetsid=6eca4ec0b2b111edb296f13fa2f023b5; _uetvid=746826c05f7411edab8c0d694cdd3714; _dy_soct=1033310.1066954.1677147557.8y02qpohpyzowckjilwg4tjrlot3q73l*1117987.1351821.1677147694*1019849.1035125.1677147694*1102807.1306611.1677147694*1090547.1263769.1677147694*1142553.1414752.1677147694*1150667.1432230.1677147694; _ga_0763T7V2BH=GS1.1.1677147559.51.1.1677147694.59.0.0; _cs_id=bc985c70-8402-a6c3-d07a-88443a8202a5.1667918728.45.1677147694.1677147559.1.1702082728033; rxvt=1677149496279|1677147558134; _ga=GA1.2.1318236147.1667918728; dtPC=2147694346_883h-vMCCBBDMJKERHEAKCIQDODHFULOPBLKTK-0e0"]';
//        $headers[] = 'Pragma: no-cache';
//        $headers[] = 'Sec-Ch-Ua: \"Chromium\";v=\"110\", \"Not A(Brand\";v=\"24\", \"Google Chrome\";v=\"110\"';
//        $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
//        $headers[] = 'Sec-Ch-Ua-Platform: \"Linux\"';
//        $headers[] = 'Sec-Fetch-Dest: document';
//        $headers[] = 'Sec-Fetch-Mode: navigate';
//        $headers[] = 'Sec-Fetch-Site: none';
//        $headers[] = 'Sec-Fetch-User: ?1';
//        $headers[] = 'Upgrade-Insecure-Requests: 1';
//        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36';
//        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
//
//        $query = curl_exec($curl_handle);
//        curl_close($curl_handle);
//
//
//        return $query;
    }

    public function getRandomUserAgent()
    {
        $userAgents = array(
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36 Edg/89.0.864.63",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 14_4_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1",
            "Mozilla/5.0 (iPad; CPU OS 14_4_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:54.0) Gecko/20100101 Firefox/54.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:54.0) Gecko/20100101 Firefox/54.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0"
        );

        return $userAgents[array_rand($userAgents)];
    }

    public function getRandomUserBrowser()
    {
        $userAgents = array(
            "curl_chrome99",
            "curl_chrome99_android",
            "curl_chrome100",
            "curl_chrome101",
            "curl_chrome104",
            "curl_chrome107",
            "curl_chrome110",
            "curl_edge99",
            "curl_edge101",
            "curl_safari15_3",
            "curl_safari15_5",
        );

        return $userAgents[array_rand($userAgents)];
    }
}
