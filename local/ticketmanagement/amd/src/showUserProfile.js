import ModalForm from 'core_form/modalform';
import Notification from 'core/notification';
import modalFactory from 'core/modal_factory';
import modalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';

export const init =() => {
    
    const users=document.querySelectorAll('.user');

    users.forEach((node)=>{
        node.addEventListener('click',(e)=>{
            showsUserProfile(e);
        })
    })
}

const showsUserProfile=(e)=>{
    e.stopPropagation();
    const userid=e.target.dataset.userid;

    const modalTitle="User Profile";
    const modalContent = '<div style="display: flex; align-items: center;">' +
                          '<img src="#" alt="Profile Picture" style="border-radius: 50%; width: 40px; height: 40px; margin-right: 10px;">' +
                          '<div><strong>Alberto Marín</strong><br>Email: alberto@gmail.com</div>' +
                          '</div>';

    
    // Create and show the modal with the dynamic title and content
    ModalFactory.create({
        title: modalTitle,
        body: modalContent
    }).then(function(modal) {
        modal.show();
    });

}