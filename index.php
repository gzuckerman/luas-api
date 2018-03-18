<?php
	/**
     * Ajax endpoint for getting luas (dublin light rail), times and geo-coded data.
     *
     * PHP version 5.5
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions are met:
     *
     * - Redistributions of source code must retain the above copyright notice,
     *   this list of conditions and the following disclaimer.
     * - Redistributions in binary form must reproduce the above copyright notice,
     *   this list of conditions and the following disclaimer in the documentation
     *   and/or other materials provided with the distribution.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
     * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
     * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
     * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
     * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
     * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
     * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
     * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
     * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
     * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
     * POSSIBILITY OF SUCH DAMAGE.
     *
     * @author  Neil Cremins <neilcremins@gmail.com> – @ncremins
     * @version 2.0
     * @link    http://www.neilcremins.com/
     *
     */

	header('Content-type: application/json');

	$action = $_REQUEST['action'];
	$station = $_REQUEST['station'];

	$stations = array (
		  'BAL'
		, 'BAW'
		, 'BEE'
		, 'BRI'
		, 'CCK'
		, 'CHA'
		, 'CHE'
		, 'COW'
		, 'CPK'
		, 'DUN'
		, 'GAL'
		, 'GLE'
		, 'HAR'
		, 'KIL'
		, 'LAU'
		, 'LEO'
		, 'MIL'
		, 'RAN'
		, 'SAN'
		, 'STI'
		, 'STS'
		, 'WIN'
		, 'ABB'
		, 'BEL'
		, 'BLA'
		, 'BLU'
		, 'BUS'
		, 'CIT'
		, 'CON'
		, 'COO'
		, 'CVN'
		, 'DRI'
		, 'FAT'
		, 'FET'
		, 'FOR'
		, 'FOU'
		, 'GDK'
		, 'GOL'
		, 'HEU'
		, 'HOS'
		, 'JAM'
		, 'JER'
		, 'KIN'
		, 'KYL'
		, 'MUS'
		, 'MYS'
		, 'RED'
		, 'RIA'
		, 'SAG'
		, 'SDK'
		, 'SMI'
		, 'SUI'
		, 'TAL'
		, 'TPT'
	);


	/**
	 * Return the contents of stations.json
	 */
	if ($action == 'stations') {
		$json = file_get_contents('stations.json');
		echo $json;
		exit;
	}
	else if ($action == 'times' && in_array($_GET['station'], $stations)) {
		getStationTimes($station);
		exit;
	}
	else {
		$error = new stdClass();
		$error->message = "Invalid station code";
		echo json_encode($error);
		exit;
	}



	/**
	 * $station should be a shortName from Stations.json
	 */
	function getStationTimes($station) {
		// Awful hack, encrypt needs to be a string for some unknown reason.
		$queryParams = array(
			  'encrypt' => 'false'
			, 'stop' => $station
			, 'action' => 'forecast'
		);

		$baseUrl = 'http://luasforecasts.rpa.ie/xml/get.ashx';

		$xmlContents = file_get_contents($baseUrl . '?' . http_build_query($queryParams));
		$xml = simplexml_load_string($xmlContents);

		$time = new stdClass();
		$time->message = (string)$xml->message;

		foreach ($xml->direction[0]->tram as $key => $tram) {
			$timeEntry = new stdClass();
			$timeEntry->direction = "Inbound";
			
			$attribs = current($tram->attributes());

			$timeEntry->dueMinutes = (string)$attribs['dueMins'];
			$timeEntry->destination = (string)$attribs['destination']; 
			$time->trams[] = $timeEntry;
		}

		foreach ($xml->direction[1]->tram as $key => $tram) {
			$timeEntry = new stdClass();
			$timeEntry->direction = "Outbound";
			
				$attribs = current($tram->attributes());

				$timeEntry->dueMinutes = (string)$attribs['dueMins'];
				$timeEntry->destination = (string)$attribs['destination'];

				$time->trams[] = $timeEntry;
		}

		echo json_encode($time);
	}
?>
