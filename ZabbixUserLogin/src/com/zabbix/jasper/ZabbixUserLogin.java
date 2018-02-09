package com.zabbix.jasper;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
//jasperreports-6.x.x.jar in the WEB-INF/lib dir of JasperReports Server
import net.sf.jasperreports.engine.JRDefaultScriptlet;
import net.sf.jasperreports.engine.JRScriptletException;

/**
 * Class used to perform user login and user logout in the Zabbix API.
 */
public class ZabbixUserLogin extends JRDefaultScriptlet {

	/**
	 * Method used to authenticate to the Zabbix API.
	 * 
	 * @param address Zabbix HTTP address
	 * @param user Zabbix user
	 * @param pass Password of the Zabbix user
	 * @throws JRScriptletException
	 * @return The auth token ID
	 * @author Thiago Murilo Diniz
	 */
	public String getAuthToken(String address, String user, String pass) throws JRScriptletException
	{
		
		String payload = "{\"jsonrpc\":\"2.0\"," +
        		"\"method\":\"user.login\"," +
        		"\"params\":{ \"user\":\"" + user + "\"," +
        		"\"password\":\"" + pass + "\"}," +
        		"\"auth\":null,\"id\":0}";
		
		return postRequest(address, payload).substring(27, 59);
		
	}
	
	/**
	 * Method used to logout of the Zabbix API.
	 * 
	 * @param address Zabbix HTTP address
	 * @param token Zabbix auth token
	 * @throws JRScriptletException
	 * @return Empty string if OK, the message returned by request if has error.
	 * @author Thiago Murilo Diniz
	 */
	public String tokenLogout(String address, String token) throws JRScriptletException
	{
		
		String payload = "{\"jsonrpc\":\"2.0\"," +
        		"\"method\":\"user.logout\"," +
        		"\"params\": []," +
        		"\"auth\": \"" + token + "\",\"id\":1}";
		
		String retorno = postRequest(address, payload);
		if(retorno.contains("true"))
		{
			return "";
		}
		else
		{
			return retorno;
		}
		
	}
	
	/**
	 * Method that performs the request to the Zabbix API.
	 * 
	 * @param address Zabbix HTTP address
	 * @param payload The content of the request.
	 * @return The request response to the API.
	 * @author Thiago Murilo Diniz
	 */
	private String postRequest(String address, String payload)
	{
		
		String output = "";
		
		try {
		
			URL url = new URL(address);
			HttpURLConnection conn = (HttpURLConnection) url.openConnection();
			conn.setDoOutput(true);
			conn.setRequestMethod("POST");
			conn.setRequestProperty("Content-Type", "application/json");
			
			OutputStream os = conn.getOutputStream();
			os.write(payload.getBytes());
			os.flush();
			int responseCode = conn.getResponseCode();
			
			if(responseCode == 200)
			{
				BufferedReader br = new BufferedReader(new InputStreamReader(conn.getInputStream()));

				output = br.readLine();
							
			}
			
			conn.disconnect();
			
		} catch(MalformedURLException e) {
        	
        	e.printStackTrace();
        	
        } catch(IOException e) {
        	
        	e.printStackTrace();
        	
        }
		
		return output;
		
	}
	
}
