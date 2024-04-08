class LogManager {
    constructor() {
      
    }
  
    saveLog(type, json) {
      const logData = {
        type: type,
        JSON: json
      };
  
      console.log(logData);
      axios.get('saveLogsToMysql.php', logData, {
        auth: this.auth
      })
      .then(response => {
        console.log('Data saved successfully!');
      })
      .catch(error => {
        console.error('Error saving data:', error);
      });
    }
  }