export default {
    methods: {
        can(value) {
            var roles =  this.$auth.user().roles;
            var result = false;
            for(var i=0; i<roles.length; i++){                
                var perms = roles[i].perms;
                for(var j=0; j<perms.length; j++){
                    if(perms[j].name == value) {
                        result = true;
                        break;
                    }
                }
                if(result)
                    break;
            }
            return result;
        }
    }
}